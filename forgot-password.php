<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission.';
    } else {
        $email = clean($conn, $_POST['email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } else {
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            // Always show generic success message to avoid leaking which emails exist
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt = $conn->prepare('UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
                $stmt->bind_param('ssi', $token, $expires, $user['id']);
                $stmt->execute();

                $resetLink = SITE_URL . '/reset-password.php?token=' . $token;
                // In production: email $resetLink to the user via PHPMailer/SMTP instead of displaying it.
                // mail($email, 'Reset your password', "Click to reset: $resetLink", "From: " . MAIL_FROM);
            }
            flash('info', 'If that email exists in our system, a password reset link has been generated.');
        }
    }
}

$infoMsg = flash('info');
$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <h2>Forgot Password</h2>
    <p class="sub">Enter your email to receive a reset link</p>

    <?php if ($infoMsg): ?><div class="alert alert-success"><?= htmlspecialchars($infoMsg) ?></div><?php endif; ?>
    <?php if (!empty($errors['general'])): ?><div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div><?php endif; ?>

    <?php if ($resetLink): ?>
        <div class="alert alert-success">
            Dev mode — no mail server configured. Use this link:<br>
            <a href="<?= htmlspecialchars($resetLink) ?>"><?= htmlspecialchars($resetLink) ?></a>
        </div>
    <?php endif; ?>

    <form method="POST" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
            <span class="error-text"><?= $errors['email'] ?? '' ?></span>
        </div>
        <button type="submit" class="btn" style="width:100%;">Send Reset Link</button>
    </form>
    <div class="auth-links"><a href="login.php">Back to login</a></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
