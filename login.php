<?php
require_once 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $db = get_db_connection();
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
            $redirect = $_POST['redirect'];
        } else {
            $redirect = ($user['role'] === 'admin') ? 'admin/index.php' : 'index.php';
        }
        
        header("Location: " . $redirect);
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}

$pageTitle = 'Login';
include 'header.php';
?>

<main class="section">
    <div class="container" style="max-width: 500px; margin: 0 auto;">
        <h2 class="section-title">Welcome Back</h2>
        <div class="feature-card" style="padding: 40px;">
            <?php if ($error): ?>
                <div style="color: #ef4444; margin-bottom: 20px; padding: 10px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 20px;">
                <?php if (isset($_GET['redirect']) || isset($_POST['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars(isset($_GET['redirect']) ? $_GET['redirect'] : $_POST['redirect']) ?>">
                <?php endif; ?>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">Email Address</label>
                    <input type="email" name="email" required style="background: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 6px; color: #333; font-family: inherit;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">Password</label>
                    <input type="password" name="password" required style="background: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 6px; color: #333; font-family: inherit;">
                </div>
                <button type="submit" name="login" class="btn btn-primary" style="padding: 15px; margin-top: 10px;">Login</button>
            </form>
            <p style="text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600;">Create Account</a>
            </p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
