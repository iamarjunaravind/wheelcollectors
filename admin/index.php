<?php
require_once '../db.php';
include 'header.php';

$db = get_db_connection();

// Get summary stats
$total_sales = $db->query("SELECT SUM(total_price) FROM orders WHERE status = 'Completed'")->fetchColumn() ?: 0;
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();

// Get recent orders
$recent_orders = $db->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// Get low stock units
$low_stock = $db->query("SELECT id, name, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5")->fetchAll();
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-indian-rupee-sign"></i>
        </div>
        <div>
            <div style="color: var(--premium-text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px;">Total Revenue</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: white;">₹<?= number_format($total_sales) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-boxes"></i>
        </div>
        <div>
            <div style="color: var(--premium-text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px;">Total Products</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: white;"><?= $total_products ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div style="color: var(--premium-text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px;">Total Users</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: white;"><?= $total_users ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <div style="color: var(--premium-text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px;">Pending Orders</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: white;"><?= $pending_orders ?></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 30px;">
    <?php if (!empty($low_stock)): ?>
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header" style="background: rgba(244, 63, 94, 0.05); border-bottom: 1px solid rgba(244, 63, 94, 0.1);">
            <h3 style="margin: 0; font-size: 1.1rem; color: #fb7185;"><i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i> Inventory Alerts</h3>
            <a href="products.php?search=&cat=" class="btn" style="background: rgba(244, 63, 94, 0.1); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.2); font-size: 0.75rem; padding: 6px 12px;">Restock</a>
        </div>
        <div style="padding: 20px;">
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($low_stock as $item): ?>
                <li style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="font-weight: 500; font-size: 0.9rem;"><?= htmlspecialchars($item['name']) ?></div>
                    <div style="background: rgba(244, 63, 94, 0.2); color: #fb7185; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;"><?= $item['stock'] ?> Left</div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header">
            <h3 style="margin: 0; font-size: 1.1rem; color: white;">Quick Links</h3>
        </div>
        <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <a href="products.php" class="btn" style="background: rgba(99, 102, 241, 0.1); color: var(--premium-primary); border: 1px solid rgba(99, 102, 241, 0.2); text-align: center; padding: 15px;">Manage Store</a>
            <a href="orders.php" class="btn" style="background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); text-align: center; padding: 15px;">Track Orders</a>
            <a href="users.php" class="btn" style="background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2); text-align: center; padding: 15px;">User Base</a>
            <a href="categories.php" class="btn" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); text-align: center; padding: 15px;">Collections</a>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;">Recent Orders</h3>
        <a href="orders.php" class="btn" style="background: rgba(255,255,255,0.05); color: var(--premium-text); padding: 8px 16px; font-size: 0.8rem;">View All</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td style="font-weight: 600; color: var(--premium-primary);">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars($order['user_name']) ?></td>
                    <td style="font-weight: 700; color: white;">₹<?= number_format($order['total_price']) ?></td>
                    <td><span class="badge badge-<?= strtolower($order['status']) ?>"><?= strtoupper($order['status']) ?></span></td>
                    <td style="color: var(--premium-text-muted); font-size: 0.9rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                    <td style="text-align: right;">
                        <a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="padding: 6px 15px; font-size: 0.8rem;">Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_orders)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--premium-text-muted); padding: 50px; font-style: italic;">No orders found yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
