<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'login_required']);
    exit;
}

$blogId = (int)($_POST['blog_id'] ?? 0);
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT id FROM likes WHERE blog_id = ? AND user_id = ?');
$stmt->bind_param('ii', $blogId, $userId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare('DELETE FROM likes WHERE id = ?');
    $stmt->bind_param('i', $existing['id']);
    $stmt->execute();
    $liked = false;
} else {
    $stmt = $conn->prepare('INSERT INTO likes (blog_id, user_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $blogId, $userId);
    $stmt->execute();
    $liked = true;
}

$count = $conn->query('SELECT COUNT(*) AS c FROM likes WHERE blog_id = ' . $blogId)->fetch_assoc()['c'];

echo json_encode(['status' => 'ok', 'liked' => $liked, 'count' => $count]);
