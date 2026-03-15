<?php
require_once 'db.php';
$db = get_db_connection();

try {
    $db->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'user'");
    echo "Column 'role' added successfully.\n";
} catch (PDOException $e) {
    echo "Column 'role' might already exist or error: " . $e->getMessage() . "\n";
}

// Check for users and promote the first one to admin if none exist
$users = $db->query("SELECT * FROM users")->fetchAll();
if (count($users) > 0) {
    $firstUser = $users[0];
    $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$firstUser['id']]);
    echo "User " . $firstUser['email'] . " promoted to admin.\n";
} else {
    // Create a default admin if no users exist
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
       ->execute(['Admin', 'admin@example.com', $password, 'admin']);
    echo "Default admin (admin@example.com / admin123) created.\n";
}
?>
