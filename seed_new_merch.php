<?php
require_once 'db.php';
$db = get_db_connection();

try {
    // 1. Add Categories
    $stmt = $db->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
    $stmt->execute(['Apparel', 'apparel', 'assets/images/kids_cat.png']);
    $apparel_id = $db->lastInsertId();

    $stmt->execute(['Accessories', 'accessories', 'assets/images/yellow_buggy.png']);
    $acc_id = $db->lastInsertId();

    // 2. Add Products
    $products_stmt = $db->prepare("INSERT INTO products (category_id, name, subtitle, price, rating, review_count, image_url, badge, description, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Apparel
    $apparel_items = [
        ['Vintage Racer T-Shirt', 'Classic Series', 1200, 4.8, 45, 'assets/images/p1.png', 'HOT', 'Premium cotton t-shirt featuring a vintage red racing car design. Perfect for heritage enthusiasts.', 1],
        ['Drift Culture Hoodie', 'Street Edition', 2500, 4.9, 12, 'assets/images/p2.png', 'NEW', 'Heavyweight hoodie with Japanese drift iconography. Comfort and style at the track.', 1],
        ['Muscle Garage Polo', 'Elite Grade', 1800, 4.7, 30, 'assets/images/p3.png', null, 'Elegant cotton polo with subtle muscle car embroidery. Sophisticated style for car shows.', 0]
    ];

    foreach ($apparel_items as $item) {
        $products_stmt->execute(array_merge([$apparel_id], $item));
    }

    // Accessories
    $acc_items = [
        ['Mustang Lid Skin', 'Muscle Pack', 800, 4.6, 18, 'assets/images/p4.png', 'POPULAR', 'High-quality vinyl laptop skin featuring the iconic Mustang lines. Scratch resistant.', 1],
        ['Hypercar Piston Skin', 'Exotic Pack', 850, 4.8, 22, 'assets/images/p1.png', 'MODERN', 'Abstract hypercar aerodynamics pattern. Precision cut for most laptops.', 0]
    ];

    foreach ($acc_items as $item) {
        $products_stmt->execute(array_merge([$acc_id], $item));
    }

    echo "Successfully seeded Apparel and Accessories!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
