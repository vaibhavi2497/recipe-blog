<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$totalBlogs = $conn->query('SELECT COUNT(*) c FROM blogs')->fetch_assoc()['c'];
$totalUsers = $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalComments = $conn->query('SELECT COUNT(*) c FROM comments')->fetch_assoc()['c'];
$totalLikes = $conn->query('SELECT COUNT(*) c FROM likes')->fetch_assoc()['c'];

$recentBlogs = $conn->query('SELECT * FROM blogs ORDER BY created_at DESC LIMIT 5');

$activePage = 'dashboard';
$pageTitle = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-content">
        <h2 class="fade-in-down">Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
        <div class="stat-cards">
            <div class="stat-card"><div class="num"><?= $totalBlogs ?></div>Total Recipes</div>
            <div class="stat-card"><div class="num"><?= $totalUsers ?></div>Registered Users</div>
            <div class="stat-card"><div class="num"><?= $totalComments ?></div>Total Comments</div>
            <div class="stat-card"><div class="num"><?= $totalLikes ?></div>Total Likes</div>
        </div>

        <h3>Recent Recipes</h3>
        <table class="admin-table">
            <tr><th>Title</th><th>Category</th><th>Posted</th><th>Action</th></tr>
            <?php while ($b = $recentBlogs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td><?= htmlspecialchars($b['category']) ?></td>
                <td><?= timeAgo($b['created_at']) ?></td>
                <td><a href="blog-edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline">Edit</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
