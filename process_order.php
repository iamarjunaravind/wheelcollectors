<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $db = get_db_connection();
    $user_id = $_SESSION['user_id'];
    $address = trim($_POST['address']);
    $total_price = (int)$_POST['total_price'];
    $cart = $_SESSION['cart'];

    try {
        $db->beginTransaction();

        // 1. Create Order
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_price, address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $total_price, $address]);
        $order_id = $db->lastInsertId();

        // 2. Create Order Items
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart as $product_id => $qty) {
            // Get current price
            $p_stmt = $db->prepare("SELECT price FROM products WHERE id = ?");
            $p_stmt->execute([$product_id]);
            $price = $p_stmt->fetchColumn();
            
            $stmt->execute([$order_id, $product_id, $qty, $price]);
        }

        $db->commit();
        
        // 3. Clear Cart
        unset($_SESSION['cart']);
        
        header("Location: success.php?order_id=" . $order_id);
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        die("Transaction failed: " . $e->getMessage());
    }
} else {
    header("Location: checkout.php");
}
?>
