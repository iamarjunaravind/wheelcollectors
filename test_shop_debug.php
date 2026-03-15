<?php
require_once 'db.php';
$db = get_db_connection();

$_GET['category'] = 1; // Simulate category=1

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category_id) {
    $query .= " AND category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $query .= " AND (name LIKE ? OR subtitle LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

echo "Query: $query\n";
echo "Params: " . print_r($params, true) . "\n";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " products.\n";
if (count($products) > 0) {
    echo "First product ID: " . $products[0]['id'] . "\n";
    echo "First product Name: " . $products[0]['name'] . "\n";
}
?>
