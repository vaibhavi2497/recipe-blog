<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';


$search = clean($conn, $_GET['search'] ?? '');
$sql = "SELECT b.*,
        (SELECT COUNT(*) FROM likes l WHERE l.blog_id = b.id) AS like_count,
        (SELECT COUNT(*) FROM comments c WHERE c.blog_id = b.id) AS comment_count
        FROM blogs b";
if ($search !== '') {
    $sql .= " WHERE b.title LIKE '%" . $search . "%' OR b.category LIKE '%" . $search . "%'";
}
$sql .= " ORDER BY b.created_at DESC";
$blogs = $conn->query($sql);

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1 class="fade-in-down">Cook, Share & <span>Savor</span></h1>
    <p class="fade-in-down">Discover mouth-watering recipes from our community of food lovers</p>
    <div class="emoji-row fade-in">
        <span class="float">🍕</span><span class="float" style="animation-delay:.2s">🍩</span>
        <span class="float" style="animation-delay:.4s">🍜</span><span class="float" style="animation-delay:.6s">🥗</span>
    </div>
    <form method="GET" style="max-width:420px;margin:24px auto 0;">
        <input type="text" name="search" placeholder="Search recipes..." value="<?= htmlspecialchars($search) ?>"
               style="width:100%;padding:12px 18px;border-radius:999px;border:1.5px solid #e5e7eb;">
    </form>
</section>

<h2 style="margin-top:40px;">Latest Recipes</h2>
<div class="blog-grid">
    <?php if ($blogs && $blogs->num_rows > 0): ?>
        <?php while ($blog = $blogs->fetch_assoc()): ?>
            <a href="blog.php?slug=<?= urlencode($blog['slug']) ?>" class="blog-card">
                <img src="<?= htmlspecialchars($blog['image'] ?: 'https://images.unsplash.com/photo-1495195134817-aeb325a55b65?w=500') ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                <div class="blog-card-body">
                    <span class="category"><?= htmlspecialchars($blog['category']) ?></span>
                    <h3><?= htmlspecialchars($blog['title']) ?></h3>
                    <p><?= htmlspecialchars(mb_strimwidth($blog['description'], 0, 90, '...')) ?></p>
                    <div class="blog-meta">
                        <span>❤️ <?= $blog['like_count'] ?> &nbsp; 💬 <?= $blog['comment_count'] ?></span>
                        <span><?= timeAgo($blog['created_at']) ?></span>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No recipes found. Check back soon!</p>
    <?php endif; ?>
</div>
</body>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

