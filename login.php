<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'index.php')); exit; }

$errors = [];
$old = ['email' => ''];
$successMsg = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
        $email = clean($conn, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $old['email'] = $email;

        if ($email === '') $errors['email'] = 'Email is required.';
        if ($password === '') $errors['password'] = 'Password is required.';

        if (empty($errors)) {
            $stmt = $conn->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user || !$user['password'] || !password_verify($password, $user['password'])) {
                $errors['general'] = 'Invalid email or password.';
            } elseif ($user['status'] === 'blocked') {
                $errors['general'] = 'Your account has been blocked. Contact admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
                exit;
            }
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <h2>Welcome Back</h2>
    <p class="sub">Login to like and comment on recipes</p>

    <?php if ($successMsg): ?><div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
    <?php if (!empty($errors['general'])): ?><div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div><?php endif; ?>

    <form method="POST" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($old['email']) ?>">
            <span class="error-text"><?= $errors['email'] ?? '' ?></span>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
            <span class="error-text"><?= $errors['password'] ?? '' ?></span>
        </div>
        <button type="submit" class="btn" style="width:100%;">Login</button>
    </form>

    <div class="divider">OR</div>
    <a href="google-login.php" class="btn-google">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" alt="Google"> Continue with Google
    </a>

    <div class="auth-links">
        <a href="forgot-password.php">Forgot password?</a><br><br>
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>
<p style="text-align:center;color:#9ca3af;font-size:.8rem;margin-top:10px;">
    Note: admins log in here too — the role on the account decides whether you land on the dashboard or the home page.
</p>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
