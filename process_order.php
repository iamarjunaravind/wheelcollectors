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

        // 1. Check Stock and Prepare Data
        foreach ($cart as $product_id => $qty) {
            $stmt = $db->prepare("SELECT name, stock, price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Product ID $product_id not found.");
            }
            
            if ($product['stock'] < $qty) {
                // Not enough stock
                $_SESSION['error'] = "Not enough stock for " . $product['name'] . ". Available: " . $product['stock'];
                $db->rollBack();
                header("Location: cart.php");
                exit;
            }
        }

        // 2. Create Order
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_price, address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $total_price, $address]);
        $order_id = $db->lastInsertId();

        // 3. Create Order Items & Reduce Stock
        $stmt_item = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        
        foreach ($cart as $product_id => $qty) {
            // Get current price again to be safe
            $p_stmt = $db->prepare("SELECT price FROM products WHERE id = ?");
            $p_stmt->execute([$product_id]);
            $price = $p_stmt->fetchColumn();
            
            // Insert order item
            $stmt_item->execute([$order_id, $product_id, $qty, $price]);
            
            // Deduct stock
            $stmt_stock->execute([$qty, $product_id]);
        }

        $db->commit();
        
        // 4. Clear Cart
        unset($_SESSION['cart']);
        
        header("Location: success.php?order_id=" . $order_id);
        exit;
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        die("Transaction failed: " . $e->getMessage());
    }
} else {
    header("Location: checkout.php");
}
?>
