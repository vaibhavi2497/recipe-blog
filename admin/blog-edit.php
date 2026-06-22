<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM blogs WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();
if (!$blog) { header('Location: blogs.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission.';
    } else {
        $title = clean($conn, $_POST['title'] ?? '');
        $category = clean($conn, $_POST['category'] ?? 'General');
        $description = trim($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $tips = trim($_POST['tips'] ?? '');

        if ($title === '') $errors['title'] = 'Title is required.';
        if ($description === '') $errors['description'] = 'Description is required.';

        $imagePath = $blog['image'];
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $errors['image'] = 'Only JPG, PNG, WEBP images are allowed.';
            } else {
                $newName = uniqid('recipe_') . '.' . $ext;
                $destDir = __DIR__ . '/../uploads/';
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destDir . $newName)) {
                    $imagePath = 'uploads/' . $newName;
                }
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare('UPDATE blogs SET title=?, description=?, ingredients=?, instructions=?, tips=?, image=?, category=? WHERE id=?');
            $stmt->bind_param('sssssssi', $title, $description, $ingredients, $instructions, $tips, $imagePath, $category, $id);
            $stmt->execute();
            flash('success', 'Recipe updated successfully!');
            header('Location: blogs.php');
            exit;
        }
    }
}

$activePage = 'blogs';
$categories = ['Breakfast','Lunch','Dinner','Dessert','Snacks','Beverages','General'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Recipe - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-content">
        <h2 class="fade-in-down">Edit Recipe</h2>
        <?php if (!empty($errors['general'])): ?><div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" data-validate novalidate style="max-width:650px;background:#fff;padding:26px;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.08);">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($blog['title']) ?>">
                <span class="error-text"><?= $errors['title'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $blog['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" required><?= htmlspecialchars($blog['description']) ?></textarea>
                <span class="error-text"><?= $errors['description'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Ingredients (one per line — each line becomes a bullet point)</label>
                <textarea name="ingredients" rows="4"><?= htmlspecialchars($blog['ingredients']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Instructions (one step per line)</label>
                <textarea name="instructions" rows="5"><?= htmlspecialchars($blog['instructions']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Tips (one per line, optional)</label>
                <textarea name="tips" rows="3"><?= htmlspecialchars($blog['tips'] ?? '') ?></textarea>
            </div>
            <?php if ($blog['image']): ?>
                <img src="../<?= htmlspecialchars($blog['image']) ?>" style="width:140px;border-radius:8px;margin-bottom:10px;">
            <?php endif; ?>
            <div class="form-group">
                <label>Replace Image (optional)</label>
                <input type="file" name="image" accept="image/*">
                <span class="error-text"><?= $errors['image'] ?? '' ?></span>
            </div>
            <button type="submit" class="btn">Update Recipe</button>
        </form>
    </div>
</div>
</body>
</html>
