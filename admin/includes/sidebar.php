<?php
// admin/includes/sidebar.php  -- expects $activePage to be set
?>
<div class="sidebar">
    <h3>🍳 Admin Panel</h3>
    <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="blogs.php" class="<?= $activePage === 'blogs' ? 'active' : '' ?>">📝 Manage Blogs</a>
    <a href="blog-add.php" class="<?= $activePage === 'blog-add' ? 'active' : '' ?>">➕ Add Blog</a>
    <a href="users.php" class="<?= $activePage === 'users' ? 'active' : '' ?>">👥 Manage Users</a>
    <a href="comments.php" class="<?= $activePage === 'comments' ? 'active' : '' ?>">💬 Comments</a>
    <a href="../index.php" target="_blank">🌐 View Site</a>
    <a href="../logout.php">🚪 Logout</a>
</div>
