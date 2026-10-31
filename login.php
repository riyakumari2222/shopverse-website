<?php
require_once __DIR__ . '/config/config.php';
if (current_user()) redirect('/index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');
 $s=db()->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email=?");$s->execute([$email]);$u=$s->fetch();
 if($u && password_verify($password,$u['password_hash'])){session_regenerate_id(true);$_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];flash('Welcome back, '.$u['name'].'!');redirect('/index.php');}
 $error='Email or password is incorrect.';
}
$pageTitle='Sign in';require __DIR__.'/includes/header.php';
?>
<section class="container auth-wrap"><div class="auth-card"><span class="eyebrow">WELCOME BACK</span><h1>Good to see you.</h1><p>Sign in to manage your cart and orders.</p><?php if($error): ?><div class="error-msg"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Email address<input type="email" name="email" required value="<?= e($_POST['email']??'') ?>" autocomplete="email"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-primary btn-full">Sign in →</button></form><p class="auth-switch">New to ShopVerse? <a href="<?= BASE_URL ?>/register.php">Create an account</a></p></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
