<?php
require_once 'db.php';
$db = get_db_connection();
$stmt = $db->prepare("SELECT * FROM products WHERE id = 116");
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
