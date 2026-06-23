<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$feedback = '';

if (isset($_GET['success_order'])) {
    $feedback = "<div class='alert alert-success'>تم حجز ومعالجة الفاتورة الحالية في خوادمنا اللوجستية بنجاح، جاري تتبع خطوط الشحن.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profile'])) {
    $name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $addr = sanitize($_POST['address']);

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmt->execute([$name, $phone, $addr, $user_id]);
    $_SESSION['full_name'] = $name;
    $feedback = "<div class='alert alert-success'>تم تعديل معلومات الهوية والموقع اللوجستي الفعلي بنجاح بنقاط التوزيع.</div>";
}

$user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->execute([$user_id]);
$profile = $user_stmt->fetch();

render_header("لوحة معلومات العميل المشغل");
echo $feedback;
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">لوحة التحكم العميل // Operator Control Station</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">تحكم بمعلومات التسليم الفعلي، واطلع على سجل الفواتير وحالات المعالجة للعتاد المشحون.</p>

<div style="display:flex; flex-wrap:wrap; gap:40px; margin-top:20px; flex-direction: row-reverse;">
    <div style="flex:1; min-width:320px; background:var(--bg-card); padding:30px; border-radius:16px; border:1px solid var(--border-color); height:fit-content; text-align: right;">
        <h3 style="color:var(--text-primary); font-size:18px; margin-bottom:20px; font-weight:600;">تعديل معلومات الهوية</h3>
        <form method="POST" action="profile.php">
            <div style="margin-bottom:15px;">
                <label>البريد الثابت للمستخدم (معرف نظام غير قابل للتعديل)</label>
                <input type="text" class="form-control" value="<?php echo sanitize($profile['email']); ?>" disabled style="background:#0a0b0d; color:var(--text-secondary); cursor:not-allowed; direction:ltr;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="full_name">الاسم المعتمد في السجلات</label>
                <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo sanitize($profile['full_name']); ?>" required style="background:var(--bg-main);">
            </div>
            <div style="margin-bottom:15px;">
                <label for="phone">رقم هاتف الطوارئ والتوصيل</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo sanitize($profile['phone']); ?>" style="background:var(--bg-main); direction:ltr;">
            </div>
            <div style="margin-bottom:25px;">
                <label for="address">موقع وعنوان التسليم المكتوب والمفصل بدقة</label>
                <textarea name="address" id="address" rows="4" class="form-control" required style="background:var(--bg-main);"><?php echo sanitize($profile['address']); ?></textarea>
            </div>
            <button type="submit" name="action_update_profile" class="btn btn-accent" style="width:100%; border-radius:10px;">حفظ وتثبيت البيانات المعدلة</button>
        </form>
    </div>

    <div style="flex:2; min-width:320px; background:var(--bg-card); padding:30px; border-radius:16px; border:1px solid var(--border-color); text-align: right;">
        <h3 style="color:var(--text-primary); font-size:18px; margin-bottom:20px; font-weight:600;">تاريخ الطلبات // Pipeline Log Status</h3>
        <?php
        $orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC");
        $orders_stmt->execute([$user_id]);
        $orders = $orders_stmt->fetchAll();

        if (count($orders) > 0):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>تاريخ الحجز</th>
                        <th>القيمة المالية الكلية</th>
                        <th>مرحلة معالجة الشحنة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                        <tr>
                            <td><strong>#<?php echo $o['order_id']; ?></strong></td>
                            <td style="font-size:13px; color:var(--text-secondary); direction:ltr; text-align:right;"><?php echo $o['order_date']; ?></td>
                            <td style="color:var(--accent-cyan); font-weight:700;">$<?php echo number_format($o['total_amount'],2); ?></td>
                            <td>
                                <span class="badge" style="background: <?php 
                                    echo $o['status'] === 'delivered' ? 'rgba(16,185,129,0.1)' : ($o['status'] === 'cancelled' ? 'rgba(239,68,68,0.1)' : 'rgba(37,99,235,0.1)'); 
                                ?>; color: <?php 
                                    echo $o['status'] === 'delivered' ? 'var(--success)' : ($o['status'] === 'cancelled' ? 'var(--danger)' : '#3b82f6'); 
                                ?>;">
                                    <?php echo strtoupper($o['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:var(--text-secondary); padding:30px 0; font-size:14px;">لا توجد أي فواتير أو طلبات جارية مسجلة في حسابك الشخصي حالياً.</p>
        <?php endif; ?>
    </div>
</div>

<?php render_footer(); ?>