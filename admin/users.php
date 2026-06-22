<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $conn->prepare("UPDATE users SET status = IF(status='active','blocked','active') WHERE id = ? AND role = 'user'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: users.php');
    exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('success', 'User deleted.');
    header('Location: users.php');
    exit;
}

$users = $conn->query("SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
$successMsg = flash('success');
$activePage = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-content">
        <h2 class="fade-in-down">Manage Users</h2>
        <?php if ($successMsg): ?><div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
        <table class="admin-table">
            <tr><th>Name</th><th>Email</th><th>Joined</th><th>Login Method</th><th>Status</th><th>Actions</th></tr>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= timeAgo($u['created_at']) ?></td>
                <td><?= $u['google_id'] ? 'Google' : 'Email' ?></td>
                <td><span class="badge badge-<?= $u['status'] ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                <td>
                    <a href="users.php?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline">
                        <?= $u['status'] === 'active' ? 'Block' : 'Unblock' ?>
                    </a>
                    <a href="users.php?delete=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user? This also removes their comments and likes.')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
