<?php
require_once __DIR__ . '/config/config.php'; require_login();
$cart=$_SESSION['cart']??[]; if(!$cart){flash('Your cart is empty.');redirect('/cart.php');}
$ids=array_map('intval',array_keys($cart));$marks=implode(',',array_fill(0,count($ids),'?'));$s=db()->prepare("SELECT * FROM products WHERE is_active=1 AND id IN ($marks)");$s->execute($ids);$products=$s->fetchAll();$subtotal=0;$lines=[];
foreach($products as $p){$qty=(int)$cart[$p['id']];if($qty<1||$qty>(int)$p['stock'])continue;$p['qty']=$qty;$p['line_total']=$qty*(float)$p['price'];$subtotal+=$p['line_total'];$lines[]=$p;}
if(!$lines){flash('Your cart items are unavailable.');redirect('/cart.php');}
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$name=trim((string)($_POST['full_name']??''));$phone=trim((string)($_POST['phone']??''));$address=trim((string)($_POST['address']??''));$city=trim((string)($_POST['city']??''));$postal=trim((string)($_POST['postal_code']??''));$payment=$_POST['payment_method']??'cod';
 if(mb_strlen($name)<2)$errors[]='Please enter your full name.';
 if(!preg_match('/^[0-9+\-\s()]{7,20}$/',$phone))$errors[]='Please enter a valid phone number.';
 if(mb_strlen($address)<8)$errors[]='Please enter a complete delivery address.';
 if(mb_strlen($city)<2)$errors[]='Please enter your city.';
 if(!preg_match('/^[A-Za-z0-9 -]{4,12}$/',$postal))$errors[]='Please enter a valid postal code.';
 if($payment!=='cod')$errors[]='Only cash on delivery is enabled in this demo.';
 if(!$errors){
  try{db()->beginTransaction();
   foreach($lines as $p){$lock=db()->prepare("SELECT stock FROM products WHERE id=? FOR UPDATE");$lock->execute([$p['id']]);$stock=$lock->fetchColumn();if($stock===false||$stock<$p['qty'])throw new RuntimeException('Stock changed for '.$p['name'].'. Please update your cart.');}
   $order=db()->prepare("INSERT INTO orders(user_id,full_name,phone,address,city,postal_code,payment_method,total,status) VALUES(?,?,?,?,?,?,?,?,?)");
   $order->execute([current_user()['id'],$name,$phone,$address,$city,$postal,'cod',$subtotal,'Placed']);$orderId=(int)db()->lastInsertId();
   $oi=db()->prepare("INSERT INTO order_items(order_id,product_id,product_name,unit_price,quantity) VALUES(?,?,?,?,?)");$up=db()->prepare("UPDATE products SET stock=stock-? WHERE id=?");
   foreach($lines as $p){$oi->execute([$orderId,$p['id'],$p['name'],$p['price'],$p['qty']]);$up->execute([$p['qty'],$p['id']]);}
   db()->commit();$_SESSION['cart']=[];redirect('/order_success.php?id='.$orderId);
  }catch(Throwable $ex){if(db()->inTransaction())db()->rollBack();$errors[]=$ex instanceof RuntimeException?$ex->getMessage():'Unable to place order. Please try again.';}
 }
}
$pageTitle='Checkout';require __DIR__.'/includes/header.php';
?>
<section class="container page-section"><div class="page-heading"><span class="eyebrow">ALMOST YOURS</span><h1>Checkout<span class="brand-dot">.</span></h1><p>Enter delivery details to place your order.</p></div>
<div class="checkout-layout"><form method="post" class="checkout-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><?php foreach($errors as $err): ?><div class="error-msg"><?= e($err) ?></div><?php endforeach; ?><h2>Delivery details</h2><div class="form-two"><label>Full name<input name="full_name" required value="<?= e($_POST['full_name']??current_user()['name']) ?>"></label><label>Phone number<input name="phone" required value="<?= e($_POST['phone']??'') ?>" autocomplete="tel"></label></div><label>Street address<input name="address" required value="<?= e($_POST['address']??'') ?>" autocomplete="street-address"></label><div class="form-two"><label>City<input name="city" required value="<?= e($_POST['city']??'') ?>" autocomplete="address-level2"></label><label>Postal code<input name="postal_code" required value="<?= e($_POST['postal_code']??'') ?>" autocomplete="postal-code"></label></div><h2>Payment method</h2><label class="payment-option"><input type="radio" name="payment_method" value="cod" checked><span><b>Cash on delivery</b><small>Pay when your order arrives. Demo checkout only.</small></span></label><button class="btn btn-primary btn-full">Place order — <?= money($subtotal) ?></button></form>
<aside class="order-summary"><h2>Your order</h2><?php foreach($lines as $p): ?><div class="summary-product"><span><?= e($p['name']) ?> × <?= (int)$p['qty'] ?></span><strong><?= money($p['line_total']) ?></strong></div><?php endforeach; ?><div><span>Shipping</span><strong class="free-shipping">Free</strong></div><div class="summary-total"><span>Total</span><strong><?= money($subtotal) ?></strong></div><p>By placing an order, you confirm your delivery details are correct.</p></aside></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
