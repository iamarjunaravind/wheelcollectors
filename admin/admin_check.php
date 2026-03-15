<?php
// admin/admin_check.php - Protect admin routes
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../login.php?redirect=$redirect");
    exit;
}
?>
