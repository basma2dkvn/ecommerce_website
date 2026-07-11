<?php
require_once 'config.php';
render_header("كتالوج الأجهزة - Digital World");

$category_filter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : '';
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$params = [];

if ($category_filter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%" . $search_query . "%";
}

if ($sort_order === 'price_low') {
    $sql .= " ORDER BY p.price ASC";
} elseif ($sort_order === 'price_high') {
    $sql .= " ORDER BY p.price DESC";
} else {
    $sql .= " ORDER BY p.product_id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<h2 style="font-size: 24px; font-weight: 700; margin-bottom: 5px; text-align: right;">المصفوفة الكاملة للمنتجات // Digital World</h2>
<p style="color: var(--text-secondary); font-size:14px; margin-bottom:30px; text-align: right;">استخدم أدوات التصفية والفرز الذكي للوصول إلى العتاد المطلوب فوراً.</p>

<form method="GET" action="products.php" style="background: var(--bg-card); padding: 20px; border-radius: 16px; margin: 20px 0; display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; border:1px solid var(--border-color);">
    <div style="flex:1; min-width:180px;">
        <label for="category_id">فئة النظام</label>
        <select name="category_id" id="category_id" class="form-control">
            <option value="0">كل فئات الأجهزة</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php if($category_filter == $cat['category_id']) echo 'selected'; ?>>
                    <?php echo sanitize($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="flex:1; min-width:180px;">
        <label for="sort">ترتيب حسب السعر</label>
        <select name="sort" id="sort" class="form-control">
            <option value="">التاريخ التنازلي</option>
            <option value="price_low">من الأقل للأعلى</option>
            <option value="price_high">من الأعلى للأقل</option>
        </select>
    </div>

    <div style="flex:2; min-width:220px;">
        <label for="query">كلمة البحث الدليلية</label>
        <input type="text" name="query" id="query" class="form-control" value="<?php echo sanitize($search_query); ?>" placeholder="أدخل اسم العتاد أو الكلمة المفتاحية...">
    </div>

    <div style="display:flex; gap:10px;">
        <button type="submit" class="btn btn-accent" style="height:48px;">تصفية النتائج</button>
        <a href="products.php" class="btn btn-danger" style="height:48px; display:inline-flex; align-items:center;">تصفير</a>
    </div>
</form>

<div class="grid">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="card">
                <div>
                    <span class="category-lbl"><?php echo sanitize($product['category_name'] ?? 'مكون نظام'); ?></span>
                    <img src="<?php echo sanitize($product['image_url']); ?>" alt="">
                    <h3><?php echo sanitize($product['name']); ?></h3>
                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                </div>
                <div>
                    <a href="product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-cyan" style="width:100%;">تفاصيل ومعالجة الطلب</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 60px 0;">لم يتم العثور على أجهزة تطابق معايير التصفية الحالية.</p>
    <?php endif; ?>
</div>

<?php render_footer(); ?>
