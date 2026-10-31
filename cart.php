<?php
require_once __DIR__ . '/config/config.php';
$cart = $_SESSION['cart'] ?? []; $items=[]; $subtotal=0;
if ($cart) {
  $ids = array_map('intval', array_keys($cart)); $marks = implode(',', array_fill(0,count($ids),'?'));
  $stmt=db()->prepare("SELECT * FROM products WHERE is_active=1 AND id IN ($marks)"); $stmt->execute($ids);
  foreach ($stmt->fetchAll() as $p) { $qty=min((int)$p['stock'],(int)$cart[$p['id']]); if($qty<1){unset($_SESSION['cart'][$p['id']]);continue;} $p['qty']=$qty; $p['line_total']=$qty*(float)$p['price']; $subtotal += $p['line_total']; $items[]=$p; }
}
$pageTitle='Your cart'; require __DIR__.'/includes/header.php';
?>
<section class="container page-section"><div class="page-heading"><span class="eyebrow">YOUR BAG</span><h1>Your cart<span class="brand-dot">.</span></h1><p>Review your finds before checking out.</p></div>
<?php if (!$items): ?><div class="empty-state"><div>♡</div><h3>Your cart is waiting for something good.</h3><p>Explore the collection and add your favourites.</p><a class="btn btn-primary" href="<?= BASE_URL ?>/index.php#products">Start shopping →</a></div>
<?php else: ?><div class="cart-layout"><form method="post" action="<?= BASE_URL ?>/cart_action.php" class="cart-items"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update">
<?php foreach($items as $p): ?><div class="cart-item"><a class="cart-thumb" href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id'] ?>"><?php if($p['image_url']): ?><img src="<?= e($p['image_url']) ?>" alt=""><?php else: ?><span><?= e(mb_strtoupper(mb_substr($p['name'],0,1))) ?></span><?php endif; ?></a><div class="cart-item-info"><a class="product-name" href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a><span class="product-category"><?= e($p['category']) ?></span><strong><?= money($p['price']) ?></strong><label class="qty-label">Qty <input type="number" name="qty[<?= (int)$p['id'] ?>]" min="0" max="<?= (int)$p['stock'] ?>" value="<?= (int)$p['qty'] ?>"></label></div><div class="cart-line-total"><strong><?= money($p['line_total']) ?></strong><button type="submit" form="remove-<?= (int)$p['id'] ?>" class="text-btn">Remove</button></div></div><?php endforeach; ?>
<button class="btn btn-outline" type="submit">Update cart</button></form>
<aside class="order-summary"><h2>Order summary</h2><div><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div><div><span>Shipping</span><strong class="free-shipping">Free</strong></div><div class="summary-total"><span>Total</span><strong><?= money($subtotal) ?></strong></div><p>Taxes, if applicable, are handled outside this demo.</p><a class="btn btn-primary btn-full" href="<?= BASE_URL ?>/checkout.php">Continue to checkout →</a><a class="continue-link" href="<?= BASE_URL ?>/index.php#products">← Continue shopping</a></aside></div>
<?php foreach($items as $p): ?><form id="remove-<?= (int)$p['id'] ?>" method="post" action="<?= BASE_URL ?>/cart_action.php" hidden><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"></form><?php endforeach; ?>
<?php endif; ?></section>
<?php require __DIR__.'/includes/footer.php'; ?>
