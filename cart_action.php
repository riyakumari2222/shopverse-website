<?php
require_once __DIR__ . '/config/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/cart.php');
verify_csrf();
$action = $_POST['action'] ?? '';
$id = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if ($action === 'add' && $id > 0) {
    $stmt = db()->prepare("SELECT id, stock, is_active FROM products WHERE id = ?"); $stmt->execute([$id]); $p = $stmt->fetch();
    $qty = max(1, min(20, (int)($_POST['quantity'] ?? 1)));
    if ($p && $p['is_active'] && $p['stock'] > 0) {
        $_SESSION['cart'][$id] = min((int)$p['stock'], (int)($_SESSION['cart'][$id] ?? 0) + $qty);
        flash('Added to your cart.');
    } else flash('This product is unavailable.');
} elseif ($action === 'update') {
    foreach (($_POST['qty'] ?? []) as $pid => $qty) {
        $pid = filter_var($pid, FILTER_VALIDATE_INT); $qty = filter_var($qty, FILTER_VALIDATE_INT);
        if (!$pid || $qty === false) continue;
        if ($qty <= 0) { unset($_SESSION['cart'][$pid]); continue; }
        $stmt = db()->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1"); $stmt->execute([$pid]); $p = $stmt->fetch();
        if ($p) $_SESSION['cart'][$pid] = min((int)$p['stock'], max(1, (int)$qty)); else unset($_SESSION['cart'][$pid]);
    }
    flash('Cart updated.');
} elseif ($action === 'remove' && $id > 0) { unset($_SESSION['cart'][$id]); flash('Item removed from cart.'); }
redirect('/cart.php');
