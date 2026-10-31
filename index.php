<?php
require_once __DIR__ . '/config/config.php';
$categories = db()->query("SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];
if (!empty($_GET['q'])) { $sql .= " AND (name LIKE ? OR description LIKE ?)"; $term = '%' . trim((string)$_GET['q']) . '%'; $params[] = $term; $params[] = $term; }
if (!empty($_GET['category'])) { $sql .= " AND category = ?"; $params[] = (string)$_GET['category']; }
$sort = $_GET['sort'] ?? 'featured';
$sorts = ['price_low' => 'price ASC', 'price_high' => 'price DESC', 'name' => 'name ASC', 'featured' => 'created_at DESC'];
$sql .= " ORDER BY " . ($sorts[$sort] ?? $sorts['featured']);
$stmt = db()->prepare($sql); $stmt->execute($params); $products = $stmt->fetchAll();
$pageTitle = 'Discover products';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="eyebrow">THE EVERYDAY UPGRADE</span>
      <h1>Find things that<br><span>feel like you.</span></h1>
      <p>Thoughtful picks for your desk, your downtime, and everything in between.</p>
      <a class="btn btn-primary" href="#products">Explore collection <span>→</span></a>
      <div class="hero-proof"><span>✓ Curated finds</span><span>✓ Easy checkout</span><span>✓ Made for you</span></div>
    </div>
    <div class="hero-art" aria-label="Decorative shopping illustration">
      <div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div>
      <div class="art-card card-back"><span>NEW</span><div class="art-shape shape-circle"></div></div>
      <div class="art-card card-front"><div class="bag-handle"></div><div class="bag-face"><b>good<br>things.</b><span>SHOPVERSE</span></div></div>
      <div class="floating-tag tag-top">YOUR NEXT FAVOURITE</div><div class="floating-tag tag-bottom">JUST FOR YOU ✦</div>
    </div>
  </div>
</section>
<section class="benefits"><div class="container benefit-row"><div><span class="benefit-icon">↗</span><span><b>Curated collection</b><small>Useful things, picked well</small></span></div><div><span class="benefit-icon">♡</span><span><b>Simple shopping</b><small>Less hassle, more happy</small></span></div><div><span class="benefit-icon">⌂</span><span><b>Order tracking</b><small>Your orders in one place</small></span></div></div></section>
<section class="section container" id="products">
  <div class="section-heading"><div><span class="eyebrow">THE COLLECTION</span><h2>Made for your everyday.</h2><p>Small upgrades. Big main-character energy.</p></div><span class="result-count"><?= count($products) ?> products</span></div>
  <form class="filters" method="get" action="<?= BASE_URL ?>/index.php#products">
    <label class="search-box"><span>⌕</span><input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search products..." aria-label="Search products"></label>
    <select name="category" aria-label="Filter by category"><option value="">All categories</option><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>" <?= (($_GET['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option><?php endforeach; ?></select>
    <select name="sort" aria-label="Sort products"><option value="featured">Featured</option><option value="price_low" <?= $sort==='price_low'?'selected':'' ?>>Price: low to high</option><option value="price_high" <?= $sort==='price_high'?'selected':'' ?>>Price: high to low</option><option value="name" <?= $sort==='name'?'selected':'' ?>>Name A–Z</option></select>
    <button class="btn btn-dark" type="submit">Apply</button>
  </form>
  <?php if (!$products): ?><div class="empty-state"><div>⌕</div><h3>No products found</h3><p>Try a different search or category.</p><a class="btn btn-outline" href="<?= BASE_URL ?>/index.php#products">Clear filters</a></div><?php else: ?>
  <div class="product-grid">
  <?php foreach ($products as $p): ?>
    <article class="product-card">
      <a class="product-image" href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id'] ?>">
        <?php if ($p['image_url']): ?><img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?><div class="product-placeholder"><span><?= e(mb_strtoupper(mb_substr($p['name'],0,1))) ?></span></div><?php endif; ?>
        <?php if ($p['badge']): ?><span class="product-badge"><?= e($p['badge']) ?></span><?php endif; ?>
      </a>
      <div class="product-info"><span class="product-category"><?= e($p['category']) ?></span><a href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id'] ?>" class="product-name"><?= e($p['name']) ?></a><div class="product-bottom"><strong><?= money($p['price']) ?></strong><form method="post" action="<?= BASE_URL ?>/cart_action.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="quantity" value="1"><button class="add-btn" aria-label="Add <?= e($p['name']) ?> to cart" <?= $p['stock'] < 1 ? 'disabled' : '' ?>>+</button></form></div></div>
    </article>
  <?php endforeach; ?>
  </div><?php endif; ?>
</section>
<section class="newsletter"><div class="container newsletter-inner"><div><span class="eyebrow">A LITTLE GOOD NEWS</span><h2>Good finds, straight to your inbox.</h2><p>Sign up is a demo placeholder — no emails are collected.</p></div><a class="btn btn-primary" href="<?= BASE_URL ?>/register.php">Create an account →</a></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
