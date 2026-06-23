<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['read_id'])) {
        $read_id = (int)$_GET['read_id'];
        $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE message_id = ?");
        $stmt->execute([$read_id]);
        $feedback = "<div class='alert alert-success'>تم تعديل رتبة القراءة للرسالة المستهدفة بنجاح داخل أعمدة السجلات.</div>";
    }
    if (isset($_GET['delete_id'])) {
        $delete_id = (int)$_GET['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE message_id = ?");
        $stmt->execute([$delete_id]);
        $feedback = "<div class='alert alert-success'>تم تصفية وسحق حزمة المراسلة النصية المحددة نهائياً من الذاكرة المخزنة.</div>";
    }
}

$messages = $pdo->query("SELECT * FROM contacts ORDER BY message_id DESC")->fetchAll();

render_header("منصة إدارة الرسائل النصية والاتصالات الواردة للمسؤول");
echo $feedback;
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">صندوق رسائل الدعم الفني للزبائن // Support Comms Terminal</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">قنوات اتصال مباشرة ومؤمنة لمعالجة الشكاوى والاستفسارات التقنية المرسلة من العملاء المجهولين أو الأعضاء.</p>

<?php if(count($messages) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>معرف الرسالة</th>
                <th>بيانات الجهة المرسلة والأصل</th>
                <th>نوع وعنوان القضية</th>
                <th>كتلة النص والحزمة المرسلة بالتفصيل</th>
                <th>وقت وساعة تسليم الرسالة للخادم</th>
                <th>أدوات خيارات المعالجة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($messages as $m): ?>
                <tr style="<?php echo !$m['is_read'] ? 'background:rgba(0, 240, 255, 0.02);' : ''; ?>">
                    <td>#<?php echo $m['message_id']; ?></td>
                    <td>
                        <strong style="color:var(--text-primary);"><?php echo sanitize($m['name']); ?></strong><br>
                        <span style="font-size:12px; color:var(--text-secondary);"><?php echo sanitize($m['email']); ?></span>
                    </td>
                    <td><span class="badge badge-primary"><?php echo sanitize($m['subject']); ?></span></td>
                    <td><div style="font-size:13px; max-width:380px; white-space: normal; word-wrap: break-word; color:var(--text-primary); line-height:1.6;"><?php echo nl2br(sanitize($m['message'])); ?></div></td>
                    <td style="font-size:12px; color:var(--text-secondary); direction:ltr; text-align:right;"><?php echo $m['submitted_at']; ?></td>
                    <td>
                        <?php if(!$m['is_read']): ?>
                            <a href="admin_messages.php?read_id=<?php echo $m['message_id']; ?>" class="btn btn-cyan" style="padding:6px 12px; font-size:11px; border-radius:6px; display:inline-block; margin-bottom:4px;">تحديد كمقروء ومفتوح</a>
                        <?php else: ?>
                            <span style="color:var(--success); font-size:12px; font-weight:700; display:block; margin-bottom:6px;">تمت المعالجة والإنجاز</span>
                        <?php endif; ?>
                        <a href="admin_messages.php?delete_id=<?php echo $m['message_id']; ?>" class="btn btn-danger" style="padding:6px 12px; font-size:11px; border-radius:6px;" onclick="return confirm('إتلاف ومسح أثر هذه الرسالة وسجلاتها من خادم الاتصال نهائياً؟');">تصفية الرسالة</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="color:var(--text-secondary); text-align:center; padding:50px; background:var(--bg-card); border-radius:16px; border:1px solid var(--border-color); margin-top:20px; font-size:14px;">لا توجد أي رسائل واردة أو معلقة داخل مصفوفة الاتصال الحالية، الذاكرة فارغة.</p>
<?php endif; ?>

<?php render_footer(); ?>