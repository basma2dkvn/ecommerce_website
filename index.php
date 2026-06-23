<?php
require_once 'config.php';
render_header("الرئيسية - VELOCE TECH");

if (isset($_GET['search']) && !empty($_GET['search'])) {
    header("Location: products.php?query=" . urlencode($_GET['search']));
    exit;
}
?>

<div style="background: radial-gradient(circle at top right, #1a1d2d, #0a0b0d); padding: 80px 60px; border-radius: 24px; border: 1px solid var(--border-color); text-align: right; margin-bottom: 50px; position:relative; overflow:hidden;">
    <div style="position:absolute; left: -100px; top: -100px; width: 400px; height: 400px; background: rgba(0, 240, 255, 0.03); filter: blur(80px); border-radius: 50%;"></div>
    <span style="color: var(--accent-cyan); font-weight: 700; font-size: 12px; letter-spacing: 2px; text-transform: uppercase;">جيل جديد من العتاد الرقمي</span>
    <h1 style="font-size: 44px; font-weight: 700; margin-top: 15px; margin-bottom: 20px; line-height: 1.2;">تسوق أحدث الأجهزة والإلكترونيات الخارقة</h1>
    <p style="font-size: 16px; margin-bottom: 35px; color: var(--text-secondary); max-width: 600px; line-height: 1.8;">عروض حصرية وجودة رائدة مخصصة للأداء العالي - توصيل سريع مع أنظمة الدفع الأكثر أماناً في المنطقة.</p>
    <a href="products.php" class="btn btn-accent" style="font-size: 15px; padding: 14px 30px; border-radius:12px;">استكشاف المخزون المتقدم</a>
</div>

<div style="margin-bottom: 50px;">
    <form method="GET" action="index.php" style="display: flex; gap: 12px; max-width: 700px; margin-right: 0; margin-left: auto;">
        <input type="text" name="search" class="form-control" placeholder="ابحث عن اسم المنتج، المواصفات التقنية، أو المعالج..." required style="margin: 0; background: var(--bg-card);">
        <button type="submit" class="btn btn-accent" style="border-radius:10px; padding:0 25px;">محرك البحث</button>
    </form>
</div>

<h2 style="font-size: 22px; font-weight: 600; margin-bottom: 20px; text-align: right;">منتجات تقنية مميزة // Featured Gear</h2>
<div class="grid">
    <?php
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id LIMIT 4");
    while ($product = $stmt->fetch()) {
        ?>
        <div class="card">
            <div>
                <span class="category-lbl"><?php echo sanitize($product['category_name'] ?? 'الأجهزة العامة'); ?></span>
                <img src="<?php echo sanitize($product['image_url']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                <h3><?php echo sanitize($product['name']); ?></h3>
                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <a href="product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-cyan" style="width:100%;">فحص المواصفات</a>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php render_footer(); ?>