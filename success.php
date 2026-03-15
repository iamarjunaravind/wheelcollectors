<?php
$pageTitle = 'Order Confirmed';
include 'header.php';
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '???';
?>

<main class="section">
    <div class="container" style="text-align: center; max-width: 600px; margin: 0 auto;">
        <div class="feature-card" style="padding: 60px;">
            <div style="width: 80px; height: 80px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 30px;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 style="font-family: 'Orbitron', sans-serif; font-size: 2rem; margin-bottom: 15px;">Order Racing!</h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 10px;">Your garage is about to get a lot more exciting.</p>
            <p style="font-weight: 700; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 40px;">Order ID: #TW-<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></p>
            
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 30px; border-radius: 12px; margin-bottom: 40px; text-align: left;">
                <h4 style="font-size: 1rem; margin-bottom: 15px;">Next Steps:</h4>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 15px;">
                    <li style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.9rem;">
                        <i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Check your email for order confirmation details.
                    </li>
                    <li style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.9rem;">
                        <i class="fa-solid fa-box-open" style="color: var(--primary);"></i> We'll notify you once your diecast machines are dispatched.
                    </li>
                </ul>
            </div>

            <div style="display: flex; gap: 20px;">
                <a href="shop.php" class="btn btn-outline" style="flex: 1;">Back to Shop</a>
                <a href="index.php" class="btn btn-primary" style="flex: 1;">Return Home</a>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
