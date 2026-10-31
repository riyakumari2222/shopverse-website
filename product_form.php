<?php
require_once __DIR__ . '/../config/config.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = ['name' => '', 'category' => '', 'description' => '', 'price' => '', 'stock' => '0', 'image_url' => '', 'badge' => '', 'is_active' => 1];
$editing = false;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        exit('Product not found');
    }
    $product = $found;
    $editing = true;
}

$categories = db()->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
    $image = trim((string) ($_POST['image_url'] ?? ''));
    $badge = trim((string) ($_POST['badge'] ?? ''));
    $active = isset($_POST['is_active']) ? 1 : 0;

    if (mb_strlen($name) < 2 || mb_strlen($name) > 160) $errors[] = 'Name must be 2–160 characters.';
    if (mb_strlen($category) < 2 || mb_strlen($category) > 80) $errors[] = 'Category must be 2–80 characters.';
    if (mb_strlen($description) < 5) $errors[] = 'Description must be at least 5 characters.';
    if ($price === false || $price < 0) $errors[] = 'Price must be a non-negative number.';
    if ($stock === false || $stock < 0) $errors[] = 'Stock must be a non-negative whole number.';
    if ($image !== '' && !filter_var($image, FILTER_VALIDATE_URL)) $errors[] = 'Image URL must be a valid URL.';

    if (!$errors) {
        if ($editing) {
            $stmt = db()->prepare("UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock = ?, image_url = ?, badge = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $category, $description, $price, $stock, $image ?: null, $badge ?: null, $active, $id]);
        } else {
            $stmt = db()->prepare("INSERT INTO products (name, category, description, price, stock, image_url, badge, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $description, $price, $stock, $image ?: null, $badge ?: null, $active]);
        }
        flash('Product saved successfully.');
        redirect('/admin/index.php');
    }

    $product = array_merge($product, [
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'price' => $_POST['price'] ?? '',
        'stock' => $_POST['stock'] ?? '0',
        'image_url' => $image,
        'badge' => $badge,
        'is_active' => $active,
    ]);
    if ($category !== '' && !in_array($category, $categories, true)) $categories[] = $category;
}

$pageTitle = $editing ? 'Edit product' : 'Add product';
require __DIR__ . '/../includes/header.php';
?>
<section class="container page-section narrow-page">
  <a class="back-link" href="<?= BASE_URL ?>/admin/index.php">← Back to dashboard</a>
  <div class="page-heading"><span class="eyebrow">CATALOGUE MANAGEMENT</span><h1><?= $editing ? 'Edit product' : 'Add a product' ?><span class="brand-dot">.</span></h1></div>
  <?php foreach ($errors as $error): ?><div class="error-msg"><?= e($error) ?></div><?php endforeach; ?>
  <form method="post" class="checkout-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Product name<input name="name" required minlength="2" maxlength="160" value="<?= e($product['name']) ?>"></label>
    <div class="form-two">
      <label>Category
        <input name="category" list="product-categories" required minlength="2" maxlength="80" value="<?= e($product['category']) ?>" placeholder="Select or enter a category">
        <datalist id="product-categories"><?php foreach ($categories as $categoryOption): ?><option value="<?= e($categoryOption) ?>"><?php endforeach; ?></datalist>
      </label>
      <label>Badge (optional)<input name="badge" maxlength="30" value="<?= e($product['badge'] ?? '') ?>" placeholder="BESTSELLER"></label>
    </div>
    <label>Description<textarea name="description" rows="5" required><?= e($product['description']) ?></textarea></label>
    <div class="form-two">
      <label>Price (₹)<input type="number" name="price" min="0" step="0.01" required value="<?= e((string) $product['price']) ?>"></label>
      <label>Stock quantity<input type="number" name="stock" min="0" step="1" required value="<?= e((string) $product['stock']) ?>"></label>
    </div>
    <label>Image URL (optional)<input type="url" name="image_url" value="<?= e($product['image_url'] ?? '') ?>" placeholder="https://example.com/product.jpg"></label>
    <label class="checkbox-label"><input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?>> Visible in storefront</label>
    <button class="btn btn-primary">Save product →</button>
  </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
