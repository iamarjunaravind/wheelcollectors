<?php
require_once 'db.php';
$db = get_db_connection();

$updates = [
    // Categories
    "UPDATE categories SET image_url = 'assets/images/drift_cat.png' WHERE slug = 'drift'",
    "UPDATE categories SET image_url = 'assets/images/offroad_cat.png' WHERE slug = 'offroad'",
    "UPDATE categories SET image_url = 'assets/images/racing_cat.png' WHERE slug = 'speed'",
    "UPDATE categories SET image_url = 'assets/images/p1.png' WHERE slug = 'custom'",
    "UPDATE categories SET image_url = 'assets/images/hero_car.png' WHERE slug = 'vault'",
    "UPDATE categories SET image_url = 'assets/images/kids_cat.png' WHERE slug = 'apparel'",
    "UPDATE categories SET image_url = 'assets/images/yellow_buggy.png' WHERE slug = 'accessories'",

    // Products (Mass updates based on category - stricter and more comprehensive)
    "UPDATE products SET image_url = 'assets/images/drift_cat.png' WHERE category_id = 1",
    "UPDATE products SET image_url = 'assets/images/offroad_cat.png' WHERE category_id = 2",
    "UPDATE products SET image_url = 'assets/images/racing_cat.png' WHERE category_id = 3",
    "UPDATE products SET image_url = 'assets/images/p1.png' WHERE category_id = 4",
    "UPDATE products SET image_url = 'assets/images/hero_car.png' WHERE category_id = 5",
    
    // Individual Products from seed_new_merch (Keep these as they are specific)
    "UPDATE products SET image_url = 'assets/images/p4.png' WHERE name = 'Vintage Racer T-Shirt'",
    "UPDATE products SET image_url = 'assets/images/p2.png' WHERE name = 'Drift Culture Hoodie'",
    "UPDATE products SET image_url = 'assets/images/p3.png' WHERE name = 'Muscle Garage Polo'",
    "UPDATE products SET image_url = 'assets/images/p4.png' WHERE name = 'Mustang Lid Skin'",
    "UPDATE products SET image_url = 'assets/images/p1.png' WHERE name = 'Hypercar Piston Skin'"
];

echo "Updating database images...\n";
foreach ($updates as $sql) {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        echo "Executed: " . substr($sql, 0, 50) . "... (" . $stmt->rowCount() . " rows affected)\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
echo "Database update complete.\n";
?>
