<?php
ob_start(); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$host = 'localhost';
$db   = 'ecommerce_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function render_header($title = "VELOCE // TECH") {
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?></title>
        <!-- خط جافاكريتا بلس الحديث للواجهات الرقمية العصرية -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --bg-main: #0a0b0d;
                --bg-card: #12141c;
                --text-primary: #f3f4f6;
                --text-secondary: #9ca3af;
                --accent-cyan: #00f0ff;
                --accent-blue: #2563eb;
                --border-color: #222533;
                --danger: #ef4444;
                --success: #10b981;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Tajawal', 'Plus Jakarta Sans', sans-serif; }
            body { background: var(--bg-main); color: var(--text-primary); min-height: 100vh; display: flex; flex-direction: column; -webkit-font-smoothing: antialiased; }
            
            /* شريط التنقل العلوي الزجاجي الفاخر */
            header { background: rgba(18, 20, 28, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color); padding: 20px 8%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
            .logo { font-size: 20px; font-weight: 700; letter-spacing: 2px; color: var(--text-primary); text-decoration: none; display: flex; align-items: center; gap: 8px; }
            .logo span { color: var(--accent-cyan); }
            nav { display: flex; align-items: center; gap: 30px; }
            nav a { color: var(--text-secondary); text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.2s, text-shadow 0.2s; }
            nav a:hover { color: var(--text-primary); text-shadow: 0 0 10px rgba(255,255,255,0.3); }
            nav a.active { color: var(--accent-cyan); font-weight: 600; }
            
            /* الحاويات الهيكلية والشبكات */
            .container { padding: 50px 8%; flex: 1; width: 100%; max-width: 1400px; margin: 0 auto; }
            .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin-top: 30px; }
            
            /* بطاقات المنتجات واللوحات بنظام الـ Obsidian UI */
            .card { background: var(--bg-card); padding: 25px; border-radius: 16px; border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s; position: relative; overflow: hidden; }
            .card:hover { transform: translateY(-5px); border-color: var(--accent-cyan); }
            .card img { width: 100%; height: 240px; object-fit: cover; border-radius: 12px; margin-bottom: 20px; filter: grayscale(10%) contrast(105%); transition: filter 0.3s; }
            .card:hover img { filter: grayscale(0%) contrast(100%); }
            .card h3 { font-size: 18px; font-weight: 600; line-height: 1.4; margin-bottom: 10px; color: var(--text-primary); text-align: right;}
            .card .category-lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-secondary); margin-bottom: 12px; font-weight: 600; display: block; text-align: right;}
            .card .price { color: var(--accent-cyan); font-weight: 700; font-size: 18px; margin-bottom: 20px; text-align: right;}
            
            /* أزرار تفاعلية بلمسات نيون مستقبلية */
            .btn { background: var(--text-primary); color: var(--bg-main); padding: 12px 24px; border: none; border-radius: 10px; text-decoration: none; cursor: pointer; display: inline-block; font-size: 14px; font-weight: 600; transition: background 0.2s, transform 0.1s, box-shadow 0.2s; text-align: center; }
            .btn:hover { background: #ffffff; box-shadow: 0 0 20px rgba(255,255,255,0.15); }
            .btn:active { transform: scale(0.98); }
            .btn-accent { background: var(--accent-blue); color: white; }
            .btn-accent:hover { background: #3b82f6; box-shadow: 0 0 20px rgba(37,99,235,0.3); }
            .btn-cyan { background: transparent; border: 1px solid var(--accent-cyan); color: var(--accent-cyan); }
            .btn-cyan:hover { background: rgba(0, 240, 255, 0.08); }
            .btn-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); }
            .btn-danger:hover { background: var(--danger); color: white; }
            
            /* عناصر الإدخال والحقول للنماذج */
            .form-control { width: 100%; padding: 14px; margin-top: 8px; background: #181a26; border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 14px; outline: none; transition: border-color 0.2s; text-align: right;}
            .form-control:focus { border-color: var(--accent-cyan); }
            label { font-size: 13px; font-weight: 500; color: var(--text-secondary); letter-spacing: 0.5px; display: block; text-align: right;}
            
            /* جداول بيانات مسطحة بدون حواف حادة */
            table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-top: 20px; text-align: right;}
            th { padding: 16px 20px; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
            td { padding: 20px; background: var(--bg-card); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); color: var(--text-primary); font-size: 14px; }
            td:first-child { border-right: 1px solid var(--border-color); border-radius: 0 12px 12px 0; }
            td:last-child { border-left: 1px solid var(--border-color); border-radius: 12px 0 0 12px; }
            
            /* صناديق الإشعارات والتنبيهات */
            .alert { padding: 16px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; border: 1px solid transparent; text-align: right;}
            .alert-success { background: rgba(16, 185, 129, 0.1); color: var(--success); border-color: rgba(16, 185, 129, 0.2); }
            .alert-danger { background: rgba(239, 110, 110, 0.1); color: var(--danger); border-color: rgba(239, 68, 68, 0.2); }
            
            /* شريط التنقل الخاص بالمسؤول المطور */
            .admin-menu { background: #131521; border-bottom: 1px solid var(--border-color); padding: 12px 8%; display: flex; justify-content: flex-start; gap: 25px; }
            .admin-menu a { color: var(--text-secondary); font-size: 13px; font-weight: 600; text-decoration: none; transition: color 0.2s; }
            .admin-menu a:hover { color: var(--accent-cyan); }
            
            .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; display: inline-block; }
            .badge-primary { background: rgba(0, 240, 255, 0.1); color: var(--accent-cyan); }
            
            footer { background: #0c0d12; border-top: 1px solid var(--border-color); text-align: center; padding: 30px; margin-top: auto; font-size: 13px; color: var(--text-secondary); }
        </style>
    </head>
    <body>
    <header>
        <a href="index.php" class="logo">VELOCE<span>//</span>TECH</a>
        <nav>
            <a href="index.php">الرئيسية</a>
            <a href="products.php">كتالوج الأجهزة</a>
            <a href="contact.php">الدعم الفني</a>
            <?php if($role === 'customer'): ?>
                <a href="cart.php">سلة المشتريات</a>
                <a href="profile.php">حسابي الشخصي</a>
                <a href="logout.php">تسجيل الخروج</a>
            <?php elseif($role === 'admin'): ?>
                <a href="admin_dashboard.php" class="active">// لوحة التحكم</a>
                <a href="logout.php">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php">تسجيل الدخول</a>
            <?php endif; ?>
        </nav>
    </header>
    <?php if($role === 'admin'): ?>
        <div class="admin-menu">
            <a href="admin_dashboard.php">لوحة الإحصائيات</a>
            <a href="admin_products.php">المخزون والمنتجات</a>
            <a href="admin_orders.php">إدارة ومتابعة الطلبات</a>
            <a href="admin_users.php">سجل المستخدمين</a>
            <a href="admin_messages.php">صندوق الرسائل الواردة</a>
        </div>
    <?php endif; ?>
    <div class="container">
    <?php
}

function render_footer() {
    ?>
    </div>
    <footer>
        <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> لشركة VELOCE TECH للتقنيات المتقدمة.</p>
    </footer>
    </body>
    </html>
    <?php
}
?>
