<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
        $name = clean($conn, $_POST['name'] ?? '');
        $email = clean($conn, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $old = ['name' => $name, 'email' => $email];

        if ($name === '') $errors['name'] = 'Name is required.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
        if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';

        if (empty($errors)) {
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors['email'] = 'Email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
                $stmt->bind_param('sss', $name, $email, $hash);
                if ($stmt->execute()) {
                    flash('success', 'Registration successful! Please login.');
                    header('Location: login.php');
                    exit;
                } else {
                    $errors['general'] = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <h2>Create Account</h2>
    <p class="sub">Join our community of food lovers</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($old['name']) ?>">
            <span class="error-text"><?= $errors['name'] ?? '' ?></span>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($old['email']) ?>">
            <span class="error-text"><?= $errors['email'] ?? '' ?></span>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required data-minlength="6">
            <span class="error-text"><?= $errors['password'] ?? '' ?></span>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
            <span class="error-text"><?= $errors['confirm_password'] ?? '' ?></span>
        </div>
        <button type="submit" class="btn" style="width:100%;">Register</button>
    </form>

    <div class="divider">OR</div>
    <a href="google-login.php" class="btn-google">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" alt="Google"> Continue with Google
    </a>

    <div class="auth-links">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
