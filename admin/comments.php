<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('success', 'Comment deleted.');
    header('Location: comments.php');
    exit;
}

$comments = $conn->query("
    SELECT c.*, u.name AS user_name, b.title AS blog_title, b.slug
    FROM comments c
    JOIN users u ON u.id = c.user_id
    JOIN blogs b ON b.id = c.blog_id
    ORDER BY c.created_at DESC
");
$successMsg = flash('success');
$activePage = 'comments';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Comments - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-content">
        <h2 class="fade-in-down">User Comments</h2>
        <?php if ($successMsg): ?><div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
        <table class="admin-table">
            <tr><th>User</th><th>Recipe</th><th>Comment</th><th>Posted</th><th>Action</th></tr>
            <?php while ($c = $comments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['user_name']) ?></td>
                <td><a href="../blog.php?slug=<?= urlencode($c['slug']) ?>" target="_blank"><?= htmlspecialchars($c['blog_title']) ?></a></td>
                <td><?= htmlspecialchars(mb_strimwidth($c['comment'], 0, 80, '...')) ?></td>
                <td><?= timeAgo($c['created_at']) ?></td>
                <td><a href="comments.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this comment?')">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
