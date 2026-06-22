<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM blogs WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('success', 'Recipe deleted.');
    header('Location: blogs.php');
    exit;
}

$blogs = $conn->query('SELECT b.*, (SELECT COUNT(*) FROM likes l WHERE l.blog_id=b.id) lc, (SELECT COUNT(*) FROM comments c WHERE c.blog_id=b.id) cc FROM blogs b ORDER BY created_at DESC');
$successMsg = flash('success');
$activePage = 'blogs';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Blogs - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-content">
        <h2 class="fade-in-down">Manage Recipes</h2>
        <?php if ($successMsg): ?><div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
        <a href="blog-add.php" class="btn" style="margin-bottom:18px;display:inline-block;">➕ Add New Recipe</a>
        <table class="admin-table">
            <tr><th>Title</th><th>Category</th><th>Likes</th><th>Comments</th><th>Posted</th><th>Actions</th></tr>
            <?php while ($b = $blogs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td><?= htmlspecialchars($b['category']) ?></td>
                <td><?= $b['lc'] ?></td>
                <td><?= $b['cc'] ?></td>
                <td><?= timeAgo($b['created_at']) ?></td>
                <td>
                    <a href="blog-edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                    <a href="blogs.php?delete=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this recipe?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
