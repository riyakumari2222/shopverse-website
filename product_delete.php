<?php
require_once __DIR__ . '/../config/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/index.php');
}

verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    flash('Invalid product.');
    redirect('/admin/index.php');
}

$stmt = db()->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);
flash($stmt->rowCount() ? 'Product removed. Existing order history was kept.' : 'Product not found.');
redirect('/admin/index.php');
