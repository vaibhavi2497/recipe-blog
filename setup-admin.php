<?php
// setup-admin.php — run this ONCE in the browser to create a working admin login,
// then DELETE this file (it's a security risk to leave it on a live server).
require_once __DIR__ . '/includes/config.php';

$email = 'admin@example.com';     // <-- change this to your new admin email
$password = 'Admin@123';         // <-- change this to your new admin password
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin', status='active' WHERE id = ?");
    $stmt->bind_param('si', $hash, $existing['id']);
    $stmt->execute();
    echo "Admin password reset. Login with: $email / $password";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES ('Site Admin', ?, ?, 'admin')");
    $stmt->bind_param('ss', $email, $hash);
    $stmt->execute();
    echo "Admin created. Login with: $email / $password";
}
echo "<br><strong>Delete this file (setup-admin.php) now.</strong>";
