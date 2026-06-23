<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "يرجى تعبئة جميع الحقول المطلوبة لإنشاء الحساب.";
    } elseif ($password !== $confirm_password) {
        $error = "كلمتا المرور غير متطابقتين. يرجى إعادة المحاولة.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
             $error = "هذا البريد الإلكتروني مسجل مسبقاً. يرجى استخدام بريد إلكتروني آخر أو تسجيل الدخول.";
        } else {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
             $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
             $success = "تم إنشاء حسابك بنجاح. سيتم تحويلك إلى صفحة تسجيل الدخول خلال لحظات...";
             header("Refresh: 2; url=login.php");
        }
    }
}

render_header("إنشاء حساب جديد");
?>

<div style="max-width: 540px; margin: 50px auto; background: var(--bg-card); padding: 40px; border-radius: 24px; border:1px solid var(--border-color); text-align: right;">
    <h2 style="text-align: center; color: var(--text-primary); font-size:24px; font-weight:700; margin-bottom:8px;">إنشاء حساب جديد</h2>
    <p style="color: var(--text-secondary); font-size:13px; text-align:center; margin-bottom:30px;">أنشئ حسابك للاستفادة من جميع خدمات المتجر وتتبع طلباتك بسهولة.</p>

```
<?php if(!empty($error)): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
<?php if(!empty($success)): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>

<form method="POST" action="register.php">
    <div style="margin-bottom:15px;">
        <label for="full_name">الاسم الكامل</label>
        <input type="text" name="full_name" id="full_name" class="form-control" required style="background:var(--bg-main);">
    </div>

    <div style="margin-bottom:15px;">
        <label for="email">البريد الإلكتروني</label>
        <input type="email" name="email" id="email" class="form-control" required style="background:var(--bg-main); direction:ltr;">
    </div>

    <div style="margin-bottom:15px;">
        <label for="phone">رقم الهاتف</label>
        <input type="text" name="phone" id="phone" class="form-control" style="background:var(--bg-main); direction:ltr;">
    </div>

    <div style="margin-bottom:15px;">
        <label for="address">العنوان</label>
        <textarea name="address" id="address" rows="3" class="form-control" style="background:var(--bg-main);"></textarea>
    </div>

    <div style="margin-bottom:15px;">
        <label for="password">كلمة المرور</label>
        <input type="password" name="password" id="password" class="form-control" required style="background:var(--bg-main); direction:ltr;">
    </div>

    <div style="margin-bottom:25px;">
        <label for="confirm_password">تأكيد كلمة المرور</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required style="background:var(--bg-main); direction:ltr;">
    </div>

    <button type="submit" class="btn btn-accent" style="width: 100%; padding: 14px; border-radius:10px;">إنشاء الحساب</button>
</form>
```

</div>

<?php render_footer(); ?>
