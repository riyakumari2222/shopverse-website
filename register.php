<?php
require_once __DIR__ . '/config/config.php';
if (current_user()) redirect('/index.php');
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf(); $name=trim((string)($_POST['name']??'')); $email=strtolower(trim((string)($_POST['email']??''))); $password=(string)($_POST['password']??'');
 if (mb_strlen($name)<2 || mb_strlen($name)>100) $errors[]='Enter a name between 2 and 100 characters.';
 if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
 if (strlen($password)<8) $errors[]='Password must be at least 8 characters.';
 if (!$errors) {
  $s=db()->prepare("SELECT id FROM users WHERE email=?");$s->execute([$email]);
  if($s->fetch()) $errors[]='An account with this email already exists.';
  else {$s=db()->prepare("INSERT INTO users(name,email,password_hash) VALUES(?,?,?)");$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);session_regenerate_id(true);$_SESSION['user']=['id'=>(int)db()->lastInsertId(),'name'=>$name,'email'=>$email,'role'=>'customer'];flash('Welcome to ShopVerse!');redirect('/index.php');}
 }
}
$pageTitle='Create account';require __DIR__.'/includes/header.php';
?>
<section class="container auth-wrap"><div class="auth-card"><span class="eyebrow">JOIN SHOPVERSE</span><h1>Create your account.</h1><p>Save your details and keep track of your orders.</p><?php foreach($errors as $err): ?><div class="error-msg"><?= e($err) ?></div><?php endforeach; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Full name<input name="name" required minlength="2" maxlength="100" value="<?= e($_POST['name']??'') ?>" autocomplete="name"></label><label>Email address<input type="email" name="email" required value="<?= e($_POST['email']??'') ?>" autocomplete="email"></label><label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"><small>At least 8 characters.</small></label><button class="btn btn-primary btn-full">Create account →</button></form><p class="auth-switch">Already have an account? <a href="<?= BASE_URL ?>/login.php">Sign in</a></p></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
