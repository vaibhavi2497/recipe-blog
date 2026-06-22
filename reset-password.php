<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$validToken = false;
$userId = null;

if ($token) {
    $stmt = $conn->prepare('SELECT id, reset_token_expires FROM users WHERE reset_token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    // Compare expiry using PHP's clock (not MySQL's NOW()) to avoid timezone mismatches
    // between the PHP server and the MySQL server.
    if ($row && $row['reset_token_expires'] && strtotime($row['reset_token_expires']) > time()) {
        $validToken = true;
        $userId = $row['id'];
    }
}

if (!$validToken) {
    $errors['general'] = 'This reset link is invalid or has expired. Please request a new one.';
}

if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?');
            $stmt->bind_param('si', $hash, $userId);
            $stmt->execute();
            flash('success', 'Password reset successful! Please login.');
            header('Location: login.php');
            exit;
        }
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <h2>Reset Password</h2>
    <p class="sub">Choose a new password</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
        <div class="auth-links"><a href="forgot-password.php">Request a new link</a></div>
    <?php else: ?>
        <form method="POST" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required data-minlength="6">
                <span class="error-text"><?= $errors['password'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
                <span class="error-text"><?= $errors['confirm_password'] ?? '' ?></span>
            </div>
            <button type="submit" class="btn" style="width:100%;">Reset Password</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
