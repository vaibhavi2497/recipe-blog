<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= SITE_NAME ?></title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar fade-in-down">
    <div class="nav-container">
        <a href="<?= SITE_URL ?>/index.php" class="logo">🍳 <?= SITE_NAME ?></a>
        <div class="nav-links">
            <a href="<?= SITE_URL ?>/index.php">Home</a>
            <?php if (isLoggedIn() && !isAdmin()): ?>
                <span class="nav-user">Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
                <a href="<?= SITE_URL ?>/logout.php" class="btn-nav">Logout</a>
            <?php elseif (isAdmin()): ?>
                <a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn-nav">Dashboard</a>
                <a href="<?= SITE_URL ?>/logout.php" class="btn-nav">Logout</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn-nav">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn-nav btn-nav-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="page-content">