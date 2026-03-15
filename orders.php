<?php
require_once 'db.php';
$pageTitle = 'My Orders';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=orders.php");
    exit;
}

$db = get_db_connection();
$user_id = $_SESSION['user_id'];
$orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$user_id]);
$order_list = $orders->fetchAll();
?>

<main class="section">
    <div class="container">
        <h2 class="section-title">My Racing Garage</h2>
        
        <?php if (empty($order_list)): ?>
            <div class="feature-card" style="padding: 60px; text-align: center;">
                <i class="fa-solid fa-box-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <p style="font-size: 1.2rem; margin-bottom: 30px;">You haven't placed any orders yet.</p>
                <a href="shop.php" class="btn btn-primary">Start Your Collection</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 30px;">
                <?php foreach ($order_list as $order): 
                    $items_stmt = $db->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $items_stmt->execute([$order['id']]);
                    $items = $items_stmt->fetchAll();
                ?>
                <div class="feature-card" style="padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px; margin-bottom: 25px;">
                        <div>
                            <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Order ID</span>
                            <h3 style="font-size: 1.1rem;">#TW-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                        </div>
                        <div style="text-align: center;">
                            <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Status</span>
                            <p style="color: #10b981; font-weight: 700; font-size: 0.9rem;"><?= $order['status'] ?></p>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Date</span>
                            <p style="font-size: 0.9rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                        <?php foreach ($items as $item): ?>
                        <div style="display: flex; gap: 15px; align-items: center; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--glass-border);">
                            <img src="<?= $item['image_url'] ?>" style="width: 60px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <div>
                                <p style="font-size: 0.9rem; font-weight: 600;"><?= $item['name'] ?></p>
                                <p style="font-size: 0.8rem; color: var(--text-muted);">Qty: <?= $item['quantity'] ?> × ₹ <?= number_format($item['price']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 60%;"><i class="fa-solid fa-location-dot" style="margin-right: 8px;"></i> <?= $order['address'] ?></p>
                        <p style="font-size: 1.2rem; font-weight: 800;">Total: <span style="color: var(--primary);">₹ <?= number_format($order['total_price']) ?></span></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
