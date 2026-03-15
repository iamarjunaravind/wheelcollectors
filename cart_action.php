<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : (isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0);
$qty = isset($_REQUEST['qty']) ? (int)$_REQUEST['qty'] : 1;

if ($id > 0) {
    switch ($action) {
        case 'add':
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] += $qty;
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
            $_SESSION['toast'] = "Item added to cart!";
            break;
        
        case 'update':
            if ($qty > 0) {
                $_SESSION['cart'][$id] = $qty;
            } else {
                unset($_SESSION['cart'][$id]);
            }
            break;
            
        case 'remove':
            unset($_SESSION['cart'][$id]);
            break;
    }
}

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';

// If adding an item, go directly to cart
if ($action == 'add') {
    $redirect = 'cart.php';
}

header("Location: $redirect");
exit;
?>
