<?php
require_once 'config.php';

$error = '';
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: " . $redirect_url);
            }
            exit;
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة. يرجى التحقق من بيانات تسجيل الدخول والمحاولة مرة أخرى.";
        }
    } else {
        $error = "يرجى تعبئة جميع الحقول المطلوبة لتسجيل الدخول.";
    }
}

render_header("تسجيل الدخول");
?>

<div style="max-width: 440px; margin: 60px auto; background: var(--bg-card); padding: 40px; border-radius: 24px; border:1px solid var(--border-color); text-align: right;">
    <h2 style="text-align: center; color: var(--text-primary); font-size:24px; font-weight:700; margin-bottom:8px;">تسجيل الدخول إلى حسابك</h2>
    <p style="color: var(--text-secondary); font-size:13px; text-align:center; margin-bottom:30px;">أدخل بيانات حسابك للوصول إلى جميع خدمات ومميزات المتجر.</p>

```
<?php if(!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" action="login.php?redirect=<?php echo urlencode($redirect_url); ?>">
    <div style="margin-bottom: 20px;">
        <label for="email">البريد الإلكتروني</label>
        <input type="email" name="email" id="email" class="form-control" required style="background:var(--bg-main); direction:ltr;">
    </div>
    <div style="margin-bottom: 25px;">
        <label for="password">كلمة المرور</label>
        <input type="password" name="password" id="password" class="form-control" required style="background:var(--bg-main); direction:ltr;">
    </div>
    <button type="submit" class="btn btn-accent" style="width: 100%; padding: 14px; border-radius:10px; margin-bottom:20px;">تسجيل الدخول</button>
</form>

<p style="text-align:center; font-size:13px; color:var(--text-secondary);">
    ليس لديك حساب؟ <a href="register.php" style="color:var(--accent-cyan); font-weight:600; text-decoration:none;">إنشاء حساب جديد</a>
</p>
```

</div>

<?php render_footer(); ?>
