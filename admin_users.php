<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_alter_role'])) {
    $target_uid = (int)$_POST['user_id'];
    $assigned_role = sanitize($_POST['role']);

    if($target_uid === (int)$_SESSION['user_id']) {
        $feedback = "<div class='alert alert-danger'>تمنع أنظمة الأمان البرمجية الحالية عزل أو إلغاء صلاحيات حسابك المسؤول الفعال حالياً بيدك.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$assigned_role, $target_uid]);
        $feedback = "<div class='alert alert-success'>تم تعديل رتبة وصلاحية المشغل بنجاح وتحديث جداول التراخيص الأمنية الكلية.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $del_uid = (int)$_GET['delete'];

    $check_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $check_orders->execute([$del_uid]);
    $order_count = $check_orders->fetchColumn();

    if ($order_count > 0) {
        $feedback = "<div class='alert alert-danger'>تنبيه أمان وسلامة البيانات العلائقية: يمتلك المستخدم المستهدف فواتير مسجلة في سجلات المعاملات المالية، تم رفض الحذف حماية لتكامل الجداول.</div>";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$del_uid]);
        $feedback = "<div class='alert alert-success'>تم تصفية وإلغاء مشغل الحساب المستهدف نهائياً من دليل الكيانات.</div>";
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY user_id ASC")->fetchAll();

render_header("دليل تراخيص المشغلين والمستخدمين العام للموقع");
echo $feedback;
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">إدارة صلاحيات المستخدمين // Secure Global Users Clearance Guide</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">قم بترقية رتب الصلاحيات أو مسح الحسابات المعطلة والخاملة من سجلات الحفظ بمرونة وبطريقة أمنة.</p>

<table>
    <thead>
        <tr>
            <th>معرف الحساب</th>
            <th>الاسم الكامل للمستخدم الكيان</th>
            <th>البريد الأساسي المعتمد للتوثيق</th>
            <th>صلاحية الوصول ومستوى التراخيص الحالية</th>
            <th>تاريخ وساعة إنشاء الحساب بالنظام</th>
            <th>عمليات مسح وإسقاط المشغل</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $u): ?>
            <tr>
                <td>#<?php echo $u['user_id']; ?></td>
                <td><strong><?php echo sanitize($u['full_name']); ?></strong></td>
                <td style="color:var(--text-secondary);"><?php echo sanitize($u['email']); ?></td>
                <td>
                    <form method="POST" action="admin_users.php" style="display:inline-flex; gap:8px; margin:0; align-items:center;">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        <select name="role" class="form-control" style="margin:0; padding:6px 12px; font-size:12px; width:auto; background:var(--bg-main);">
                            <option value="customer" <?php if($u['role']==='customer') echo 'selected'; ?>>حساب عميل عادي</option>
                            <option value="admin" <?php if($u['role']==='admin') echo 'selected'; ?>>رخصة مسؤول كاملة</option>
                        </select>
                        <button type="submit" name="action_alter_role" class="btn btn-cyan" style="padding:6px 12px; font-size:12px; border-radius:8px;">تعديل الرخصة</button>
                    </form>
                </td>
                <td style="font-size:13px; color:var(--text-secondary); direction:ltr; text-align:right;"><?php echo $u['created_at']; ?></td>
                <td>
                    <?php if($u['user_id'] !== $_SESSION['user_id']): ?>
                        <a href="admin_users.php?delete=<?php echo $u['user_id']; ?>" class="btn btn-danger" style="padding:6px 12px; font-size:12px; border-radius:8px;" onclick="return confirm('تنفيذ عملية إسقاط ومسح نهائي لهذا المستخدم المشغل من ذاكرة النظام كلياً؟');">حذف الحساب</a>
                    <?php else: ?>
                        <span style="font-size:11px; color:var(--accent-cyan); font-style:italic; font-weight:600;">مشغل الأمان الفعال حالياً للغرفة</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php render_footer(); ?>