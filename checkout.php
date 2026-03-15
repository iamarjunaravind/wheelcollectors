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
        <h2 class="section-title">Finalize Order</h2>
        <div class="checkout-grid">
            <!-- Shipping Info -->
            <div class="feature-card" style="padding: 40px;">
                <h3 style="font-size: 1.2rem; margin-bottom: 30px;">Shipping Details</h3>
                <form method="POST" action="process_order.php" style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 0.9rem; font-weight: 600;">Delivery Address</label>
                        <textarea name="address" required placeholder="Enter your full street address, city, and pincode..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 15px; border-radius: 8px; color: white; min-height: 120px; font-family: 'Outfit', sans-serif;"></textarea>
                    </div>
                    
                    <h3 style="font-size: 1.2rem; margin: 20px 0 10px;">Payment Method</h3>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; padding: 20px; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
                        <i class="fa-solid fa-truck-fast" style="color: #10b981; font-size: 1.2rem;"></i>
                        <span style="font-size: 0.9rem; font-weight: 600;">Cash on Delivery (COD) Available</span>
                    </div>
                    
                    <input type="hidden" name="total_price" value="<?= $total ?>">
                    <button type="submit" class="btn btn-primary" style="padding: 20px; margin-top: 20px;">Place Order Now</button>
                </form>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="feature-card" style="padding: 30px;">
                    <h3 style="font-size: 1.2rem; margin-bottom: 25px;">Review Order</h3>
                    <div class="checkout-items" style="max-height: 350px; overflow-y: auto; padding-right: 10px; margin-bottom: 25px;">
                        <?php foreach ($products as $p): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <img src="<?= $p['image_url'] ?>" style="width: 50px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <p style="font-size: 0.9rem; font-weight: 600;"><?= $p['name'] ?></p>
                                    <p style="font-size: 0.75rem; color: var(--text-muted);">Qty: <?= $cart[$p['id'] ] ?></p>
                                </div>
                            </div>
                            <span style="font-weight: 700; font-size: 0.9rem;">₹ <?= number_format($p['price'] * $cart[$p['id']]) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.3rem; margin-top: 20px;">
                        <span>Final Total</span>
                        <span style="color: var(--primary);">₹ <?= number_format($total) ?></span>
                    </div>
                    
                    <div style="margin-top: 40px; padding: 20px; background: rgba(255,255,255,0.02); border-radius: 8px; border: 1px dashed var(--glass-border);">
                        <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center;">
                            <i class="fa-solid fa-lock" style="margin-right: 5px;"></i> SECURE CHECKOUT SSL ENCRYPTED
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
