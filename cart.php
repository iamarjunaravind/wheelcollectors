<?php
require_once 'db.php';
$pageTitle = 'Your Cart';
include 'header.php';

$db = get_db_connection();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $products = $db->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll();
}
?>

<main class="section">
    <div class="container">
        <h2 class="section-title">Shopping Cart</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(244, 63, 94, 0.1); color: #fb7185; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(244, 63, 94, 0.2); display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart)): ?>
            <div class="feature-card" style="padding: 60px; text-align: center;">
                <i class="fa-solid fa-cart-shopping" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <p style="font-size: 1.2rem; margin-bottom: 30px;">Your cart is empty.</p>
                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <div class="cart-items">
                    <?php foreach ($products as $p): 
                        $qty = $cart[$p['id']];
                        $subtotal = $p['price'] * $qty;
                        $total += $subtotal;
                        
                        // Simulate MRP and Discount for visual styling (since database lacks these fields)
                        $discount_percent = rand(15, 60); 
                        $mrp = $p['price'] * (100 / (100 - $discount_percent));
                        $saved = ($mrp - $p['price']) * $qty;
                    ?>
                    <div class="cart-item-card">
                        <div class="cart-item-image">
                            <img src="<?= $p['image_url'] ?>" alt="<?= $p['name'] ?>">
                        </div>
                        <div class="cart-item-details">
                            <h3 class="cart-product-title"><?= $p['name'] ?></h3>
                            <div class="cart-product-meta">
                                <span class="cart-subtitle"><?= $p['subtitle'] ?></span>
                                <div class="cart-rating">
                                    <span class="rating-box"><?= number_format($p['rating'], 1) ?> <i class="fa-solid fa-star"></i></span>
                                    <span class="review-count">(<?= number_format($p['review_count']) ?>)</span>
                                </div>
                            </div>
                            
                            <div class="cart-pricing-row">
                                <span class="price-original">₹<?= number_format($mrp) ?></span>
                                <span class="current-price">₹<?= number_format($p['price']) ?></span>
                                <span class="discount-tag"><?= $discount_percent ?>% off</span>
                            </div>

                            <div class="cart-actions-row">
                                <div class="qty-control">
                                    <a href="cart_action.php?action=update&id=<?= $p['id'] ?>&qty=<?= $qty-1 ?>" class="qty-btn <?= ($qty <= 1) ? 'disabled' : '' ?>">-</a>
                                    <div class="qty-input"><?= $qty ?></div>
                                    <a href="cart_action.php?action=update&id=<?= $p['id'] ?>&qty=<?= $qty+1 ?>" class="qty-btn">+</a>
                                </div>
                                <a href="cart_action.php?action=remove&id=<?= $p['id'] ?>" class="remove-btn">REMOVE</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="feature-card" style="padding: 30px;">
                        <h3 style="font-size: 1.2rem; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">Order Summary</h3>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span>₹ <?= number_format($total) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--text-muted);">Shipping</span>
                            <span style="color: #10b981;">FREE</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-weight: 800; font-size: 1.3rem;">
                            <span>Total</span>
                            <span style="color: var(--primary);">₹ <?= number_format($total) ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary desktop-checkout-btn" style="width: 100%; display: block; text-align: center; margin-top: 30px; padding: 18px;">Proceed to Checkout</a>
                    </div>
                    <a href="shop.php" class="btn btn-outline" style="width: 100%; display: block; text-align: center; margin-top: 15px; padding: 18px; border: 1px solid var(--glass-border);">Continue Shopping</a>
                </div>
            </div>

            <!-- Sticky Mobile Bottom Bar -->
            <div class="mobile-bottom-bar">
                <div class="mobile-total">
                    <span class="mobile-total-label">Total:</span>
                    <span class="mobile-total-price">₹<?= number_format($total) ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary mobile-checkout-btn">Place Order</a>
            </div>
            
        </div>
    <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
