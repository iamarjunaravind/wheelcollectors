<?php
require_once 'db.php';
$pageTitle = 'Checkout';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$db = get_db_connection();
$ids = implode(',', array_keys($cart));
$products = $db->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll();
$total = 0;
foreach ($products as $p) {
    $total += $p['price'] * $cart[$p['id']];
}
?>

<main class="section">
    <div class="container">
        <div class="section-header" style="text-align: left; margin-bottom: 40px;">
            <h2 class="section-title">Checkout</h2>
            <p style="color: var(--text-muted); margin-top: 5px;">Secure checkout powered by SSL encryption</p>
        </div>
        
        <div class="checkout-grid">
            <!-- Shipping Info -->
            <div class="feature-card" style="padding: 40px;">
                <h3 style="font-size: 1.2rem; margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-truck" style="color: var(--primary); font-size: 1rem;"></i> Shipping Details
                </h3>
                <form method="POST" action="process_order.php" style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 0.9rem; font-weight: 600;">Delivery Address</label>
                        <textarea name="address" required class="premium-input" placeholder="Enter your full street address, city, and pincode..." style="min-height: 120px;"></textarea>
                    </div>
                    
                    <h3 style="font-size: 1.2rem; margin: 20px 0 10px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-credit-card" style="color: var(--primary); font-size: 1rem;"></i> Payment Method
                    </h3>
                    <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; padding: 20px; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
                        <i class="fa-solid fa-truck-fast" style="color: #10b981; font-size: 1.2rem;"></i>
                        <span style="font-size: 0.9rem; font-weight: 600; color: #065f46;">Cash on Delivery (COD) Available</span>
                    </div>
                    
                    <input type="hidden" name="total_price" value="<?= $total ?>">
                    <button type="submit" class="btn btn-primary" style="padding: 20px; margin-top: 20px; width: 100%; border-radius: 8px; font-weight: 700; letter-spacing: 1px;">
                        PLACE ORDER NOW
                    </button>
                    <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin-top: 10px;">
                        <i class="fa-solid fa-shield-check" style="margin-right: 5px;"></i> By placing an order, you agree to our Terms & Conditions.
                    </p>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary-sidebar">
                <div class="feature-card" style="padding: 30px; position: sticky; top: 100px;">
                    <h3 style="font-size: 1.2rem; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-basket-shopping" style="color: var(--primary); font-size: 1rem;"></i> Your Order
                    </h3>
                    <div class="checkout-items" style="max-height: 400px; overflow-y: auto; padding-right: 10px; margin-bottom: 25px;">
                        <?php foreach ($products as $p): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <div style="width: 60px; height: 60px; background: #fff; border: 1px solid rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; padding: 5px;">
                                    <img src="<?= $p['image_url'] ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-dark);"><?= $p['name'] ?></span>
                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Qty: <?= $cart[$p['id']] ?></span>
                                </div>
                            </div>
                            <span style="font-weight: 800; font-size: 1rem; color: var(--text-dark);">₹ <?= number_format($p['price'] * $cart[$p['id']]) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="border-top: 2px dashed rgba(0,0,0,0.1); padding-top: 20px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.4rem;">
                            <span>Order Total</span>
                            <span style="color: var(--primary);">₹ <?= number_format($total) ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid rgba(0,0,0,0.03); text-align: center;">
                        <p style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-lock" style="color: #10b981;"></i> 256-BIT SSL SECURE CHECKOUT
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
