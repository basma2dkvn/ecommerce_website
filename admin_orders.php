<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_change_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize($_POST['status']);

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);
    $feedback = "<div class='alert alert-success'>تم تعديل حالة شحن الفاتورة المقررة وحفظ المتغير الجديد في خوادم المعالجة التلقائية.</div>";
}

$orders = $pdo->query("SELECT o.*, u.full_name as client_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC")->fetchAll();

render_header("أنظمة التحكم ومتابعة الشحنات اللوجستية");
echo $feedback;
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">إدارة الطلبات والشحنات // Global Order Pipeline Controls</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">قم بتغيير مراحل المعالجة والنقل وتحديث الحالات الأمنية للفواتير المشحونة للعملاء.</p>

<table>
    <thead>
        <tr>
            <th>رقم الفاتورة</th>
            <th>اسم العميل أو الكيان المشغل المشتري</th>
            <th>تاريخ وساعة تفعيل الفاتورة</th>
            <th>عائدات الفاتورة الكلية</th>
            <th>التحكم اليدوي بمراحل المعالجة والشحن</th>
            <th>عنوان وإحداثيات الموقع اللوجستي المتفق عليه للوصول</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($orders as $o): ?>
            <tr>
                <td><strong>#<?php echo $o['order_id']; ?></strong></td>
                <td><?php echo sanitize($o['client_name']); ?></td>
                <td style="font-size:13px; color:var(--text-secondary); direction:ltr; text-align:right;"><?php echo $o['order_date']; ?></td>
                <td style="font-weight:700; color:var(--accent-cyan);">$<?php echo number_format($o['total_amount'], 2); ?></td>
                <td>
                    <form method="POST" action="admin_orders.php" style="display:inline-flex; gap:8px; margin:0; align-items:center;">
                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                        <select name="status" class="form-control" style="margin:0; padding:6px 12px; font-size:12px; width:auto; background:var(--bg-main);">
                            <option value="pending" <?php if($o['status']==='pending') echo 'selected'; ?>>قيد المراجعة المعلقة</option>
                            <option value="paid" <?php if($o['status']==='paid') echo 'selected'; ?>>تم تأكيد استلام الدفع</option>
                            <option value="shipped" <?php if($o['status']==='shipped') echo 'selected'; ?>>خارج للتوصيل والترتيب</option>
                            <option value="delivered" <?php if($o['status']==='delivered') echo 'selected'; ?>>تم التسليم والإغلاق الفني</option>
                            <option value="cancelled" <?php if($o['status']==='cancelled') echo 'selected'; ?>>ملغي من المسؤول</option>
                        </select>
                        <button type="submit" name="action_change_status" class="btn btn-accent" style="padding:6px 12px; font-size:12px; border-radius:8px;">تحديث</button>
                    </form>
                </td>
                <td style="font-size:12px; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-secondary);"><?php echo sanitize($o['shipping_address']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php render_footer(); ?>