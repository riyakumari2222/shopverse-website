<?php require_once __DIR__ . '/../config/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#101828">
  <title><?= e($pageTitle ?? SITE_NAME) ?> | <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <script defer src="<?= BASE_URL ?>/assets/js/app.js"></script>
</head>
<body>
<div class="announcement">✨ Welcome to ShopVerse — discover your next favourite thing</div>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><span class="brand-mark">S</span> ShopVerse<span class="brand-dot">.</span></a>
    <button class="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>
    <nav class="main-nav" aria-label="Main navigation">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <a href="<?= BASE_URL ?>/index.php#products">Shop</a>
      <?php if (current_user()): ?>
        <a href="<?= BASE_URL ?>/orders.php">My orders</a>
        <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
          <a href="<?= BASE_URL ?>/admin/index.php">Admin</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/admin/login.php">Admin sign in</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Sign in</a>
        <a href="<?= BASE_URL ?>/admin/login.php">Admin sign in</a>
      <?php endif; ?>
      <a class="cart-link" href="<?= BASE_URL ?>/cart.php">Cart <span class="cart-count"><?= cart_count() ?></span></a>
    </nav>
  </div>
</header>
<main>
<?php if ($notice = flash()): ?><div class="container"><div class="flash"><?= e($notice) ?></div></div><?php endif; ?>
