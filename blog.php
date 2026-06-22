<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = clean($conn, $_GET['slug'] ?? '');
$stmt = $conn->prepare('SELECT * FROM blogs WHERE slug = ?');
$stmt->bind_param('s', $slug);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if (!$blog) {
    header('Location: index.php');
    exit;
}

// Handle comment submission (login required)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if (csrfCheck($_POST['csrf_token'] ?? '')) {
        $commentText = clean($conn, $_POST['comment']);
        if ($commentText !== '') {
            $stmt = $conn->prepare('INSERT INTO comments (blog_id, user_id, comment) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $blog['id'], $_SESSION['user_id'], $commentText);
            $stmt->execute();
        }
    }
    header('Location: blog.php?slug=' . urlencode($slug) . '#comments');
    exit;
}

// Like count + whether current user liked it
$likeCountRes = $conn->query('SELECT COUNT(*) AS c FROM likes WHERE blog_id = ' . (int)$blog['id']);
$likeCount = $likeCountRes->fetch_assoc()['c'];
$userLiked = false;
if (isLoggedIn()) {
    $stmt = $conn->prepare('SELECT id FROM likes WHERE blog_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $blog['id'], $_SESSION['user_id']);
    $stmt->execute();
    $userLiked = $stmt->get_result()->num_rows > 0;
}

// Comments
$stmt = $conn->prepare('SELECT c.*, u.name FROM comments c JOIN users u ON u.id = c.user_id WHERE c.blog_id = ? ORDER BY c.created_at DESC');
$stmt->bind_param('i', $blog['id']);
$stmt->execute();
$comments = $stmt->get_result();

$pageTitle = $blog['title'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="blog-detail">
    <span class="category"><?= htmlspecialchars($blog['category']) ?></span>
    <h1><?= htmlspecialchars($blog['title']) ?></h1>
    <p style="color:#9ca3af;">Posted <?= timeAgo($blog['created_at']) ?></p>
    <img src="<?= htmlspecialchars($blog['image'] ?: 'https://images.unsplash.com/photo-1495195134817-aeb325a55b65?w=800') ?>" alt="">

    <h3>Recipe Details</h3>
    <p><?= nl2br(htmlspecialchars($blog['description'])) ?></p>

    <?php if ($blog['ingredients']): ?>
        <h3>🛒 Ingredients</h3>
        <?= toBulletList($blog['ingredients']) ?>
    <?php endif; ?>

    <?php if ($blog['instructions']): ?>
        <h3>👩‍🍳 Instructions</h3>
        <?= toBulletList($blog['instructions']) ?>
    <?php endif; ?>

    <?php if (!empty($blog['tips'])): ?>
        <h3>💡 Tips</h3>
        <?= toBulletList($blog['tips']) ?>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
        <button class="like-btn" data-blog-id="<?= $blog['id'] ?>">
            <span class="heart"><?= $userLiked ? '❤️' : '🤍' ?></span>
            <span id="like-count"><?= $likeCount ?></span> Likes
        </button>
    <?php else: ?>
        <p>❤️ <?= $likeCount ?> Likes</p>
        <div class="locked-msg">🔒 <a href="login.php">Login</a> to like and comment on this recipe.</div>
    <?php endif; ?>

    <div class="comment-box" id="comments">
        <h3>Comments (<?= $comments->num_rows ?>)</h3>

        <?php if (isLoggedIn()): ?>
            <form method="POST" data-validate novalidate style="margin-bottom:24px;">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="form-group">
                    <textarea name="comment" rows="3" required placeholder="Share your thoughts..."></textarea>
                    <span class="error-text"></span>
                </div>
                <button type="submit" class="btn btn-sm">Post Comment</button>
            </form>
        <?php endif; ?>

        <?php while ($c = $comments->fetch_assoc()): ?>
            <div class="comment-item">
                <div class="meta">
                    <strong><?= htmlspecialchars($c['name']) ?></strong>
                    <span><?= timeAgo($c['created_at']) ?></span>
                </div>
                <p><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
