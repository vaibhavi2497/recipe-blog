<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$code = $_GET['code'] ?? null;
if (!$code) {
    flash('error', 'Google login was cancelled or failed.');
    header('Location: login.php');
    exit;
}

// Step 1: Exchange authorization code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$postFields = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$tokenData = json_decode($response, true);

if (empty($tokenData['access_token'])) {
    flash('error', 'Could not authenticate with Google.');
    header('Location: login.php');
    exit;
}

// Step 2: Fetch user profile
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $tokenData['access_token']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$profileResponse = curl_exec($ch);
curl_close($ch);
$profile = json_decode($profileResponse, true);

if (empty($profile['email'])) {
    flash('error', 'Could not retrieve your Google profile.');
    header('Location: login.php');
    exit;
}

$googleId = $profile['id'];
$email = $conn->real_escape_string($profile['email']);
$name = $conn->real_escape_string($profile['name'] ?? 'Google User');
$avatar = $profile['picture'] ?? null;

// Step 3: Find or create user
$stmt = $conn->prepare('SELECT id, name, role, status FROM users WHERE email = ? OR google_id = ?');
$stmt->bind_param('ss', $email, $googleId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    if ($user['status'] === 'blocked') {
        flash('error', 'Your account has been blocked. Contact admin.');
        header('Location: login.php');
        exit;
    }
    // Link google_id if logging in for first time via Google on an existing email account
    $stmt = $conn->prepare('UPDATE users SET google_id = ?, avatar = ? WHERE id = ?');
    $stmt->bind_param('ssi', $googleId, $avatar, $user['id']);
    $stmt->execute();
    $userId = $user['id'];
    $role = $user['role'];
    $userName = $user['name'];
} else {
    $stmt = $conn->prepare('INSERT INTO users (name, email, google_id, avatar, role) VALUES (?, ?, ?, ?, "user")');
    $stmt->bind_param('ssss', $name, $email, $googleId, $avatar);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $role = 'user';
    $userName = $name;
}

$_SESSION['user_id'] = $userId;
$_SESSION['name'] = $userName;
$_SESSION['email'] = $email;
$_SESSION['role'] = $role;

header('Location: ' . ($role === 'admin' ? 'admin/dashboard.php' : 'index.php'));
exit;
