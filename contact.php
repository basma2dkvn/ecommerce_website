<?php
require_once 'config.php';

$message_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $msg_body = sanitize($_POST['message']);

    if (empty($name) || empty($email) || empty($msg_body)) {
        $message_status = "<div class='alert alert-danger'>فشلت عملية التحقق. يرجى ملء كافة الحقول الأساسية لإرسال الرسالة الحالية.</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $msg_body]);
        $message_status = "<div class='alert alert-success'>تم تأمين وحفظ قناة الاتصال. رسالتك قيد المعالجة الآن من المهندسين المسؤولين.</div>";
    }
}

render_header("الدعم الفني - Digital World");
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">اتصل بنا // مركز اتصالات الدعم الفني والمراسلة</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">أرسل تقارير الخلل، الاستفسارات الخاصة بالبنية التحتية، أو المقترحات البرمجية لمهندسينا.</p>

<?php echo $message_status; ?>

<div style="display: flex; flex-wrap: wrap; gap: 40px; margin-top: 20px; flex-direction: row-reverse;">
    <div style="flex: 2; min-width: 320px; background: var(--bg-card); padding: 35px; border-radius: 16px; border:1px solid var(--border-color);">
        <h3 style="margin-bottom: 25px; color:var(--text-primary); font-size:18px; font-weight:600; text-align: right;">أرسل لنا رسالة // Transmission Network Input</h3>
        <form method="POST" action="contact.php">
            <div style="margin-bottom:18px;">
                <label for="name">الاسم الكامل للجهة المراسلة</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div style="margin-bottom:18px;">
                <label for="email">عنوان البريد الإلكتروني لشبكة العودة</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div style="margin-bottom:18px;">
                <label for="subject">تصنيف أولوية التوجيه والمراسلة</label>
                <select name="subject" id="subject" class="form-control" style="padding:14px;">
                    <option value="استفسار">استفسار تقني عام // Inquiry</option>
                    <option value="شكوى خلل">تقرير خلل في واجهة النظام // Complaint</option>
                    <option value="اقتراح">اقتراح تطوير معمارية برمجية // Suggestion</option>
                </select>
            </div>
            <div style="margin-bottom:25px;">
                <label for="message">نص الرسالة أو سجل الخطأ المكتوب بالتفصيل</label>
                <textarea name="message" id="message" rows="6" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-accent" style="width:100%; padding:14px; border-radius:10px;">حقن وإرسال حزمة المراسلة</button>
        </form>
    </div>

    <div style="flex: 1; min-width: 280px; background: #131521; border:1px solid var(--border-color); color: white; padding: 35px; border-radius: 16px; display:flex; flex-direction:column; justify-content: space-between; text-align: right;">
        <div>
            <h3 style="color:var(--accent-cyan); margin-bottom:25px; font-size:18px; font-weight:600;">إحداثيات الاتصال الفعلي // HQ Coordinates</h3>
            <p style="margin-bottom: 20px; font-size:14px; color:var(--text-secondary);"><strong style="color:white; display:block; margin-bottom:4px;">📍 المقر الرئيسي:</strong> قطاع غزة، فلسطين - شارع عسقلان التقني</p>
            <p style="margin-bottom: 20px; font-size:14px; color:var(--text-secondary);"><strong style="color:white; display:block; margin-bottom:4px;">📞 الهاتف السلكي الأساسي:</strong> +970-59-XXXXXXX</p>
            <p style="margin-bottom: 20px; font-size:14px; color:var(--text-secondary);"><strong style="color:white; display:block; margin-bottom:4px;">✉️ البريد الإلكتروني المركزي:</strong> core@Digital World.com</p>
        </div>
        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; font-size:12px; color:var(--text-secondary);">
            <p style="color:white; font-weight:600; margin-bottom:5px;">ساعات نافذة الصيانة الفعالة:</p>
            <p>الأحد - الخميس: 08:00 صباحاً - 04:00 مساءً بالتوقيت المحلي</p>
        </div>
    </div>
</div>

<?php render_footer(); ?>
