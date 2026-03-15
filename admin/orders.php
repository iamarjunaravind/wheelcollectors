<?php
require_once '../db.php';
$db = get_db_connection();
$message = '';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    $message = "Order status updated to $status!";
}

$order_details = null;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$_GET['id']]);
    $order_details = $stmt->fetch();
    
    if ($order_details) {
        $stmt = $db->prepare("SELECT oi.*, p.name as product_name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$_GET['id']]);
        $order_items = $stmt->fetchAll();
    }
}

$orders = $db->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();

include 'header.php';
?>

<?php if ($order_details): ?>
<div class="admin-card" style="margin-bottom: 30px; border-left: 5px solid var(--admin-primary);">
    <div class="admin-card-header">
        <h3 style="margin: 0;">Order Details #ORD-<?= str_pad($order_details['id'], 5, '0', STR_PAD_LEFT) ?></h3>
        <a href="orders.php" class="btn btn-sm" style="border: 1px solid #e2e8f0; color: #64748b;">Back to List</a>
    </div>
    <div style="padding: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        <div>
            <h4 style="margin-top: 0; color: #64748b; font-size: 0.875rem; text-transform: uppercase;">Customer Information</h4>
            <p><strong>Name:</strong> <?= htmlspecialchars($order_details['user_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order_details['user_email']) ?></p>
            <p><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order_details['address'])) ?></p>
        </div>
        <div>
            <h4 style="margin-top: 0; color: #64748b; font-size: 0.875rem; text-transform: uppercase;">Order Summary</h4>
            <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order_details['created_at'])) ?></p>
            <p><strong>Total Amount:</strong> <span style="font-size: 1.25rem; font-weight: 700; color: var(--admin-primary);">₹<?= number_format($order_details['total_price']) ?></span></p>
            
            <form method="POST" action="orders.php?id=<?= $order_details['id'] ?>" style="margin-top: 20px; display: flex; gap: 10px;">
                <input type="hidden" name="order_id" value="<?= $order_details['id'] ?>">
                <select name="status" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; flex: 1;">
                    <option value="Pending" <?= $order_details['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Processing" <?= $order_details['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $order_details['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Completed" <?= $order_details['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $order_details['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-primary" style="padding: 10px 20px;">Update</button>
            </form>
        </div>
    </div>
    <div style="padding: 0 25px 25px;">
        <h4 style="color: #64748b; font-size: 0.875rem; text-transform: uppercase; margin-bottom: 15px;">Order Items</h4>
        <table class="admin-table" style="border: 1px solid #e2e8f0; border-radius: 8px;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="../<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                            <span style="font-weight: 600;"><?= htmlspecialchars($item['product_name']) ?></span>
                        </div>
                    </td>
                    <td>₹<?= number_format($item['price']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td style="text-align: right; font-weight: 600;">₹<?= number_format($item['price'] * $item['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 style="margin: 0; font-size: 1.1rem;">All Orders</h3>
    </div>
    <div style="padding: 25px;">
        <?php if ($message && !$order_details): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr style="<?= ($order_details && $order_details['id'] == $order['id']) ? 'background: #f8fafc;' : '' ?>">
                    <td>#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($order['user_name']) ?></td>
                    <td>₹<?= number_format($order['total_price']) ?></td>
                    <td>
                        <?php 
                        $status_class = '';
                        switch($order['status']) {
                            case 'Pending': $status_class = '#fef3c7; color: #92400e;'; break;
                            case 'Completed': $status_class = '#d1fae5; color: #065f46;'; break;
                            case 'Cancelled': $status_class = '#fee2e2; color: #991b1b;'; break;
                            case 'Shipped': $status_class = '#e0e7ff; color: #4338ca;'; break;
                            default: $status_class = '#f1f5f9; color: #475569;';
                        }
                        ?>
                        <span class="badge" style="background: <?= $status_class ?>">
                            <?= $order['status'] ?>
                        </span>
                    </td>
                    <td style="font-size: 0.875rem; color: #64748b;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                    <td style="text-align: right;">
                        <a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">No orders found in the database.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
