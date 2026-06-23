<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$feedback = '';
$edit_mode = false;
$edit_p = [
    'product_id'=>'', 'name'=>'', 'description'=>'', 'price'=>'', 'stock_quantity'=>'', 'image_url'=>'', 'category_id'=>''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_save'])) {
        $p_id = (int)$_POST['product_id'];
        $name = sanitize($_POST['name']);
        $desc = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        $img = sanitize($_POST['image_url']);
        $cat_id = (int)$_POST['category_id'] > 0 ? (int)$_POST['category_id'] : null;

        if ($p_id > 0) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock_quantity=?, image_url=?, category_id=? WHERE product_id=?");
            $stmt->execute([$name, $desc, $price, $stock, $img, $cat_id, $p_id]);
            $feedback = "<div class='alert alert-success'>تم تعديل وتحديث بيانات مواصفات الجهاز المستهدف بداخل سجلات التخزين الفعالة.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, image_url, category_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $desc, $price, $stock, $img, $cat_id]);
            $feedback = "<div class='alert alert-success'>تم إدخال وتمرير العتاد التقني الجديد بنجاح في مصفوفات التوزيع الكلي.</div>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['edit'])) {
        $edit_id = (int)$_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$edit_id]);
        $fetched = $stmt->fetch();
        if ($fetched) { $edit_p = $fetched; $edit_mode = true; }
    }
    if (isset($_GET['delete'])) {
        $del_id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$del_id]);
        $feedback = "<div class='alert alert-success'>تم مسح وتصفية الكيان المستهدف نهائياً من خوادم قاعدة البيانات الكلية للمتجر.</div>";
    }
}

$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

render_header("لوحة تجميع وإدارة مخازن المنتجات");
echo $feedback;
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">إدارة المنتجات التقنية // Master Product Inventory Manager</h2>
<p style="color:var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">أضف عناصر نظام جديدة، أو قم بتحديث كميات وأسعار القطع المتوفرة حالياً في المستودعات الحقيقية.</p>

<div style="display:flex; flex-wrap:wrap; gap:40px; margin-top:20px; flex-direction: row-reverse;">
    <div style="flex:1; min-width:320px; background:var(--bg-card); padding:30px; border-radius:16px; border:1px solid var(--border-color); height:fit-content; text-align: right;">
        <h3 style="color:var(--text-primary); font-size:18px; margin-bottom:20px; font-weight:600;"><?php echo $edit_mode ? 'تحديث حقول البيانات المستهدفة' : 'حقن عنصر جديد في المخزن'; ?></h3>
        <form method="POST" action="admin_products.php">
            <input type="hidden" name="product_id" value="<?php echo $edit_p['product_id']; ?>">
            
            <div style="margin-bottom:12px;">
                <label for="name">اسم الموديل والجهاز التقني</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo sanitize($edit_p['name']); ?>" required style="background:var(--bg-main);">
            </div>
            <div style="margin-bottom:12px;">
                <label for="category_id">تصنيف العتاد والمجموعات الفئوية</label>
                <select name="category_id" id="category_id" class="form-control" style="background:var(--bg-main); padding:14px;">
                    <option value="0">غير مصنف // النطاق العام</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if($edit_p['category_id'] == $cat['category_id']) echo 'selected'; ?>><?php echo sanitize($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label for="price">قيمة التسعير المقررة بالدولار ($ USD Base)</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?php echo $edit_p['price']; ?>" required style="background:var(--bg-main); direction:ltr;">
            </div>
            <div style="margin-bottom:12px;">
                <label for="stock_quantity">حجم الوحدات الفيزيائية المتاحة للتوزيع في المخازن</label>
                <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="<?php echo $edit_p['stock_quantity'] ? $edit_p['stock_quantity'] : 0; ?>" required style="background:var(--bg-main); direction:ltr;">
            </div>
            <div style="margin-bottom:12px;">
                <label for="image_url">رابط الصورة الرقمية المباشرة من شبكة CDN ومواقع التخزين</label>
                <input type="url" name="image_url" id="image_url" class="form-control" value="<?php echo sanitize($edit_p['image_url']); ?>" placeholder="https://images.unsplash.com/example" style="background:var(--bg-main); direction:ltr;">
            </div>
            <div style="margin-bottom:20px;">
                <label for="description">المواصفات التقنية الكاملة والقطع والشرائح المدمجة بالجهاز</label>
                <textarea name="description" id="description" rows="4" class="form-control" style="background:var(--bg-main);"></textarea>
            </div>
            <button type="submit" name="action_save" class="btn btn-accent" style="width:100%; border-radius:10px;"><?php echo $edit_mode ? 'تطبيق وإجراء الكتابة الفوقية للبيانات' : 'تأكيد وحفظ القطعة داخل النظام العلائقي للموقع'; ?></button>
            <?php if($edit_mode): ?> <a href="admin_products.php" class="btn btn-danger" style="width:100%; text-align:center; margin-top:8px; border-radius:10px; display:block;">إلغاء تركيز التعديل الحالي</a> <?php endif; ?>
        </form>
    </div>

    <div style="flex:2; min-width:320px; text-align: right;">
        <h3 style="font-size:18px; font-weight:600; margin-bottom:10px;">جدول العرض الكلي لوحدات المخزن والمستودع الحالية</h3>
        <table>
            <thead>
                <tr>
                    <th>معرف القطعة</th>
                    <th>الاسم التقني للمنتج</th>
                    <th>الفئة المصنفة</th>
                    <th>السعر النقدي</th>
                    <th>القطع المتوفرة بالمخزن</th>
                    <th>الإجراءات والعمليات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p): ?>
                    <tr>
                        <td>#<?php echo $p['product_id']; ?></td>
                        <td><strong><?php echo sanitize($p['name']); ?></strong></td>
                        <td style="font-size:13px; color:var(--text-secondary);"><?php echo sanitize($p['category_name'] ?? 'مكون نظام مدمج للعتاد'); ?></td>
                        <td style="font-weight:700; color:var(--accent-cyan);">$<?php echo number_format($p['price'], 2); ?></td>
                        <td style="font-weight:700; color: <?php echo $p['stock_quantity'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>;"><?php echo $p['stock_quantity']; ?> وحدة جاهزة</td>
                        <td>
                            <a href="admin_products.php?edit=<?php echo $p['product_id']; ?>" class="btn btn-cyan" style="padding:6px 12px; font-size:12px; border-radius:6px; margin-left:5px;">تعديل</a>
                            <a href="admin_products.php?delete=<?php echo $p['product_id']; ?>" class="btn btn-danger" style="padding:6px 12px; font-size:12px; border-radius:6px;" onclick="return confirm('تأكيد مسح هذه القطعة كلياً من دائرة المبيعات وأنظمة العرض والطلب؟');">مسح</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php render_footer(); ?>