<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_update'])) {
        $cart_id = (int)$_POST['cart_id'];
        $new_qty = (int)$_POST['quantity'];

        if ($new_qty <= 0) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            $stmt->execute([$new_qty, $cart_id, $user_id]);
        }

        $feedback = "<div class='alert alert-success'>تم تحديث الكميات بنجاح.</div>";
    }

    if (isset($_POST['action_remove'])) {
        $cart_id = (int)$_POST['cart_id'];
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);

        $feedback = "<div class='alert alert-success'>تم حذف العنصر من سلة المشتريات.</div>";
    }

    if (isset($_POST['action_checkout'])) {
        $stmt = $pdo->prepare("
            SELECT ci.*, p.price, p.stock_quantity, p.name
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $items = $stmt->fetchAll();

        if (count($items) === 0) {
            $feedback = "<div class='alert alert-danger'>لا يمكن إتمام الطلب لأن السلة فارغة.</div>";
        } else {

            $user_stmt = $pdo->prepare("SELECT address FROM users WHERE user_id = ?");
            $user_stmt->execute([$user_id]);
            $user_addr = $user_stmt->fetchColumn();

            if (empty($user_addr)) {
                $feedback = "<div class='alert alert-danger'>يرجى إضافة عنوان التوصيل في ملفك الشخصي لإتمام الطلب.</div>";
            } else {

                $total_order = 0;
                foreach ($items as $i) {
                    $total_order += ($i['price'] * $i['quantity']);
                }

                $pdo->beginTransaction();

                try {
                    $ins_order = $pdo->prepare("
                        INSERT INTO orders (user_id, total_amount, shipping_address, status)
                        VALUES (?, ?, ?, 'pending')
                    ");
                    $ins_order->execute([$user_id, $total_order, $user_addr]);
                    $order_id = $pdo->lastInsertId();

                    $ins_item = $pdo->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                        VALUES (?, ?, ?, ?)
                    ");

                    $upd_stock = $pdo->prepare("
                        UPDATE products SET stock_quantity = stock_quantity - ?
                        WHERE product_id = ?
                    ");

                    foreach ($items as $item) {
                        $ins_item->execute([
                            $order_id,
                            $item['product_id'],
                            $item['quantity'],
                            $item['price']
                        ]);

                        $upd_stock->execute([
                            $item['quantity'],
                            $item['product_id']
                        ]);
                    }

                    $del_cart = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
                    $del_cart->execute([$user_id]);

                    $pdo->commit();

                    header("Location: profile.php?success_order=1");
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $feedback = "<div class='alert alert-danger'>فشل إتمام الطلب. يرجى المحاولة مرة أخرى.</div>";
                }
            }
        }
    }
}

render_header("سلة المشتريات - VELOCE TECH");
echo $feedback;

$stmt = $pdo->prepare("
    SELECT ci.*, p.name, p.price, p.image_url
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
?>

<h2 style="font-size:24px; font-weight:700; margin-bottom:5px; text-align: right;">
سلة المشتريات
</h2>

<p style="color:var(--text-secondary); font-size:14px; margin-bottom:20px; text-align: right;">
راجع طلبك قبل إتمام عملية الدفع.
</p>

<?php if (count($cart_items) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>الصورة</th>
                <th>المنتج</th>
                <th>السعر</th>
                <th>الكمية</th>
                <th>الإجمالي</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $running_total = 0;
            foreach ($cart_items as $item):
                $subtotal = $item['price'] * $item['quantity'];
                $running_total += $subtotal;
            ?>
                <tr>
                    <td>
                        <img src="<?php echo sanitize($item['image_url']); ?>"
                             style="width:60px; height:60px; object-fit:cover; border-radius:10px;">
                    </td>

                    <td><strong><?php echo sanitize($item['name']); ?></strong></td>

                    <td>$<?php echo number_format($item['price'], 2); ?></td>

                    <td>
                        <form method="POST" action="cart.php" style="display:inline-flex; gap:8px;">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="number" name="quantity"
                                   value="<?php echo $item['quantity']; ?>" min="1"
                                   style="width:65px; padding:8px;">
                            <button type="submit" name="action_update" class="btn btn-cyan"
                                    style="padding:8px 12px; font-size:11px;">
                                تحديث
                            </button>
                        </form>
                    </td>

                    <td style="color:var(--accent-cyan); font-weight:600;">
                        $<?php echo number_format($subtotal, 2); ?>
                    </td>

                    <td>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="action_remove" class="btn btn-danger"
                                    style="padding:8px 12px; font-size:11px;">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:40px; background:var(--bg-card); padding:30px; border-radius:16px;">
        <h3 style="margin-bottom:20px;">
            الإجمالي:
            <span style="color:var(--accent-cyan); font-weight:700;">
                $<?php echo number_format($running_total, 2); ?>
            </span>
        </h3>

        <form method="POST" action="cart.php">
            <button type="submit" name="action_checkout" class="btn btn-accent"
                    style="font-size:15px; padding:14px 35px; border-radius:10px;">
                إتمام الطلب
            </button>
        </form>
    </div>

<?php else: ?>
    <div style="text-align:center; padding:60px 0; background:var(--bg-card); border-radius:16px;">
        <p style="color:var(--text-secondary); margin-bottom:20px;">
            سلة المشتريات فارغة حالياً.
        </p>
        <a href="products.php" class="btn btn-accent">
            تصفح المنتجات
        </a>
    </div>
<?php endif; ?>

<?php render_footer(); ?>