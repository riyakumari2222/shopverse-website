<?php
require_once __DIR__ . '/config/config.php';

$adminExists = (bool) db()->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Name must be between 2 and 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 12) {
        $errors[] = 'Use a password with at least 12 characters.';
    }

    if (!$errors) {
        $lockAcquired = false;
        $adminId = null;
        try {
            $lockAcquired = (int) db()->query("SELECT GET_LOCK('shopverse_first_admin', 10)")->fetchColumn() === 1;
            if (!$lockAcquired) {
                $errors[] = 'Admin setup is busy. Please try again.';
            } elseif (db()->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn()) {
                $adminExists = true;
                $errors[] = 'An admin account has already been created.';
            } else {
                $stmt = db()->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn()) {
                    $errors[] = 'That email is already registered. Use a different email for the admin account.';
                }
            }

            if ($lockAcquired && !$adminExists && !$errors) {
                $stmt = db()->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                $adminId = (int) db()->lastInsertId();
            }
        } finally {
            if ($lockAcquired) {
                db()->query("SELECT RELEASE_LOCK('shopverse_first_admin')");
            }
        }

        if ($adminId !== null) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $adminId, 'name' => $name, 'email' => $email, 'role' => 'admin'];
            flash('Admin account created. Welcome to the control panel.');
            redirect('/admin/index.php');
        }
    }
}

$pageTitle = 'Create the admin account';
require __DIR__ . '/includes/header.php';
?>
<section class="container auth-wrap">
  <div class="auth-card">
    <span class="eyebrow">ONE-TIME SETUP</span>
    <h1>Create the admin account.</h1>
    <?php if ($adminExists): ?>
      <p>The single admin account is already set up. Admin account creation is now closed.</p>
      <a class="btn btn-primary btn-full" href="<?= BASE_URL ?>/admin/login.php">Go to admin sign in</a>
    <?php else: ?>
      <p>This setup is available only until the first admin account is created. Use a strong password.</p>
      <?php foreach ($errors as $error): ?><div class="error-msg"><?= e($error) ?></div><?php endforeach; ?>
      <form method="post" class="stack-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Admin name<input name="name" required minlength="2" maxlength="100" value="<?= e($name) ?>" autocomplete="name"></label>
        <label>Admin email<input type="email" name="email" required value="<?= e($email) ?>" autocomplete="email"></label>
        <label>Password<input type="password" name="password" required minlength="12" autocomplete="new-password"><small>At least 12 characters.</small></label>
        <button class="btn btn-primary btn-full">Create admin account</button>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
