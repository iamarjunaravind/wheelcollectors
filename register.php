<?php
require_once 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $db = get_db_connection();
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed])) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}

$pageTitle = 'Register';
include 'header.php';
?>

<main class="section">
    <div class="container" style="max-width: 500px; margin: 0 auto;">
        <h2 class="section-title">Create Account</h2>
        <div class="feature-card" style="padding: 40px;">
            <?php if ($error): ?>
                <div style="color: #ef4444; margin-bottom: 20px; padding: 10px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444;">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="color: #10b981; margin-bottom: 20px; padding: 10px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981;">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">Full Name</label>
                    <input type="text" name="name" required style="background: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 6px; color: #333; font-family: inherit;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">Email Address</label>
                    <input type="email" name="email" required style="background: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 6px; color: #333; font-family: inherit;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">Password</label>
                    <input type="password" name="password" required style="background: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 6px; color: #333; font-family: inherit;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">Confirm Password</label>
                    <input type="password" name="confirm_password" required style="background: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 6px; color: #333; font-family: inherit;">
                </div>
                <button type="submit" name="register" class="btn btn-primary" style="padding: 15px; margin-top: 10px;">Sign Up</button>
            </form>
            <p style="text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600;">Login</a>
            </p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
