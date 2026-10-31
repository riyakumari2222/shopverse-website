<?php
require_once __DIR__ . '/config/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1"); $stmt->execute([$id]); $p = $stmt->fetch();
if (!$p) { http_response_code(404); $pageTitle='Product not found'; require __DIR__.'/includes/header.php'; echo '<div class="container empty-state"><h2>Product not found</h2><a href="'.BASE_URL.'/index.php">Back to shop</a></div>'; require __DIR__.'/includes/footer.php'; exit; }
$pageTitle = $p['name']; require __DIR__ . '/includes/header.php';
?>
<section class="container product-detail">
  <div class="detail-image"><?php if ($p['image_url']): ?><img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>"><?php else: ?><div class="product-placeholder large"><span><?= e(mb_strtoupper(mb_substr($p['name'],0,1))) ?></span></div><?php endif; ?></div>
  <div class="detail-copy"><a class="back-link" href="<?= BASE_URL ?>/index.php#products">← Back to collection</a><span class="product-category"><?= e($p['category']) ?></span><h1><?= e($p['name']) ?></h1><div class="detail-price"><?= money($p['price']) ?></div><p class="detail-description"><?= nl2br(e($p['description'])) ?></p><p class="stock-line"><?= $p['stock'] > 0 ? '✓ In stock — '.$p['stock'].' available' : 'Currently out of stock' ?></p>
  <?php if ($p['stock'] > 0): ?><form class="add-detail" method="post" action="<?= BASE_URL ?>/cart_action.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><label>Quantity <input type="number" name="quantity" min="1" max="<?= (int)$p['stock'] ?>" value="1"></label><button class="btn btn-primary">Add to cart — <?= money($p['price']) ?></button></form><?php endif; ?>
  <div class="detail-assurance"><span>✓ Secure account login</span><span>✓ Simple order history</span><span>✓ Demo COD checkout</span></div></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
