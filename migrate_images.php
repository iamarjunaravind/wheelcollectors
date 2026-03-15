<?php
require_once 'db.php';
$db = get_db_connection();

try {
    // 1. Create product_images table
    $db->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER,
        image_path TEXT NOT NULL,
        is_primary INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    echo "Table 'product_images' created successfully.\n";

    // 2. Migrate existing images
    $products = $db->query("SELECT id, image_url FROM products")->fetchAll();
    $stmt = $db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
    
    $count = 0;
    foreach ($products as $product) {
        if (!empty($product['image_url'])) {
            $stmt->execute([$product['id'], $product['image_url'], 1]);
            $count++;
        }
    }
    echo "Migrated $count primary images from 'products' table.\n";

    // 3. Create products upload directory
    $upload_dir = __DIR__ . '/assets/images/products';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
        echo "Created directory: $upload_dir\n";
    }

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
