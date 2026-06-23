<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$unread_messages = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();

render_header("مركز القيادة البرمجية والتحكم الكلي للمسؤول");
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">لوحة تحكم المسؤول // Core Architecture Dashboard</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:35px; text-align: right;">تم توثيق والتحقق من صلاحيات مشغل النظام الحالي: <strong><?php echo sanitize($_SESSION['full_name']); ?></strong></p>

<div class="grid" style="margin-bottom:40px; direction:rtl;">
    <div class="card" style="border-top: 4px solid var(--accent-cyan);">
        <span style="font-size:12px; font-weight:600; text-transform:uppercase; color:var(--text-secondary); display:block; text-align:right;">عدد منتجات المخزن الكلي</span>
        <p style="font-size:36px; font-weight:700; margin:15px 0; color:var(--text-primary); text-align:right;"><?php echo $total_products; ?> جهاز</p>
        <a href="admin_products.php" class="btn btn-cyan" style="font-size:12px; padding:8px 16px; width:fit-content; margin-right:auto; margin-left:0;">تعديل المخزون الحالي</a>
    </div>

    <div class="card" style="border-top: 4px solid #3b82f6;">
        <span style="font-size:12px; font-weight:600; text-transform:uppercase; color:var(--text-secondary); display:block; text-align:right;">الفواتير والتدفقات اللوجستية</span>
        <p style="font-size:36px; font-weight:700; margin:15px 0; color:var(--text-primary); text-align:right;"><?php echo $total_orders; ?> طلب معلق</p>
        <a href="admin_orders.php" class="btn btn-cyan" style="font-size:12px; padding:8px 16px; width:fit-content; margin-right:auto; margin-left:0;">إدارة خطوط الشحن</a>
    </div>

    <div class="card" style="border-top: 4px solid var(--success);">
        <span style="font-size:12px; font-weight:600; text-transform:uppercase; color:var(--text-secondary); display:block; text-align:right;">المشغلين والمستخدمين المسجلين</span>
        <p style="font-size:36px; font-weight:700; margin:15px 0; color:var(--text-primary); text-align:right;"><?php echo $total_users; ?> حساب نشط</p>
        <a href="admin_users.php" class="btn btn-cyan" style="font-size:12px; padding:8px 16px; width:fit-content; margin-right:auto; margin-left:0;">مراجعة رخص الولوج</a>
    </div>

    <div class="card" style="border-top: 4px solid var(--danger);">
        <span style="font-size:12px; font-weight:600; text-transform:uppercase; color:var(--text-secondary); display:block; text-align:right;">رسائل وطلبات الدعم الفني غير المقروءة</span>
        <p style="font-size:36px; font-weight:700; margin:15px 0; color:var(--text-primary); text-align:right;"><?php echo $unread_messages; ?> رسالة واردة</p>
        <a href="admin_messages.php" class="btn btn-cyan" style="font-size:12px; padding:8px 16px; width:fit-content; margin-right:auto; margin-left:0;">فتح منصة الاتصالات</a>
    </div>
</div>

<div style="background:var(--bg-card); padding:30px; border-radius:16px; border:1px solid var(--border-color); text-align: right;">
    <h3 style="font-size:16px; font-weight:600; color:var(--text-primary); margin-bottom:10px;">دليل التحكم والعمليات الإدارية</h3>
    <p style="line-height:1.7; color:var(--text-secondary); font-size:14px;">استخدم الأشرطة والروابط العلوية المدمجة ذات التصميم المحدث للوصول الفوري والمباشر لكل لوحات التعديل والإدخال والحذف المباشر لبيانات الخوادم العلائقية الحالية للموقع بمرونة تامة وأمان.</p>
</div>

<?php render_footer(); ?>