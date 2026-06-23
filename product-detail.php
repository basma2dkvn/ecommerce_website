<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    render_header("خطأ في العثور على العتاد");
    echo "<div class='alert alert-danger'>المنتج المطلوب غير متوفر حالياً في مستودعاتنا الفعالة.</div>";
    echo "<a href='products.php' class='btn'>العودة للمعرض</a>";
    render_footer();
    exit;
}

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=" . urlencode("product-detail.php?id=" . $product_id));
        exit;
    }
    
    $qty = (int)$_POST['quantity'];
    if ($qty < 1 || $qty > $product['stock_quantity']) {
        $feedback = "<div class='alert alert-danger'>عذراً، الكمية المطلوبة تتجاوز المتاح في مخزن التوزيع الحقيقي.</div>";
    } else {
        $cart_check = $pdo->prepare("SELECT cart_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $cart_check->execute([$_SESSION['user_id'], $product_id]);
        $existing = $cart_check->fetch();

        if ($existing) {
            $new_qty = $existing['quantity'] + $qty;
            $update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ?");
            $update->execute([$new_qty, $existing['cart_id']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->execute([$_SESSION['user_id'], $product_id, $qty]);
        }
        $feedback = "<div class='alert alert-success'>تم حجز وإدخال العنصر بنجاح إلى واجهة سلة المشتريات.</div>";
    }
}

render_header(sanitize($product['name']) . " // مواصفات العتاد");
?>

<a href="products.php" style="text-decoration:none; display:inline-block; margin-bottom:30px; font-size:14px; font-weight:600; color:var(--text-secondary);">← العودة إلى قائمة الأجهزة</a>

<?php echo $feedback; ?>

<div style="display: flex; flex-wrap: wrap; gap: 50px; background: var(--bg-card); padding: 40px; border-radius: 24px; border: 1px solid var(--border-color); flex-direction: row-reverse;">
    <div style="flex: 1; min-width: 320px; text-align: center;">
        <img src="<?php echo sanitize($product['image_url']); ?>" alt="" style="width: 100%; max-height: 420px; border-radius: 16px; object-fit: cover;">
    </div>
    <div style="flex: 1; min-width: 320px; display:flex; flex-direction:column; justify-content:center; text-align:right;">
        <span class="badge badge-primary" style="align-self: flex-start; margin-bottom:15px; margin-right:0; margin-left:auto;"><?php echo sanitize($product['category_name'] ?? 'مكون نظام أساسي'); ?></span>
        <h1 style="font-size:32px; font-weight:700; color:var(--text-primary); margin-bottom:10px;"><?php echo sanitize($product['name']); ?></h1>
        
        <p style="font-size: 28px; font-weight: 700; color: var(--accent-cyan); margin-bottom: 25px;">$<?php echo number_format($product['price'], 2); ?></p>
        
        <div style="margin-bottom: 30px; border-top:1px solid var(--border-color); padding-top:20px;">
            <h3 style="font-size:14px; text-transform:uppercase; color:var(--text-secondary); margin-bottom:10px;">المخطط التفصيلي للوحدة</h3>
            <p style="line-height: 1.8; color: var(--text-secondary); font-size:15px;"><?php echo nl2br(sanitize($product['description'])); ?></p>
        </div>

        <div style="margin-bottom:25px; font-size:13px; color:var(--text-secondary);">
            حالة توفر المخزون: 
            <span style="color: <?php echo $product['stock_quantity'] > 0 ? 'var(--success)':'var(--danger)'; ?>; font-weight: 700; margin-right:5px;">
                <?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' وحدات جاهزة للتسليم فوراً' : 'غير متوفر حالياً في خط التوزيع'; ?>
            </span>
        </div>

        <?php if($product['stock_quantity'] > 0): ?>
            <form method="POST" action="product-detail.php?id=<?php echo $product_id; ?>" style="background: #181a26; padding: 25px; border-radius: 16px; border: 1px solid var(--border-color);">
                <div style="margin-bottom: 20px;">
                    <label for="quantity" style="font-weight: 600; color:var(--text-primary);">تحديد الكمية المطلوبة</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 120px; text-align:center; margin-right:0; margin-left:auto;">
                </div>
                <button type="submit" name="action_add_cart" class="btn btn-accent" style="width: 100%; padding: 14px; border-radius:10px;">تخصيص وإضافة للسلة</button>
            </form>
        <?php else: ?>
            <button class="btn" disabled style="background:#222533; color:var(--text-secondary); width:100%; cursor:not-allowed; border-radius:10px;">دائرة الإنتاج مغلقة مؤقتاً</button>
        <?php endif; ?>
    </div>
</div>

<?php render_footer(); ?>