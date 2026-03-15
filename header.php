<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | THE VAULT' : 'THE VAULT | Hot Wheels Collector Shop'; ?></title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="humberger-menu-overlay"></div>
    <div class="humberger-menu-wrapper">
        <div class="humberger-menu-logo">
            <a href="index.php" style="font-family: 'Oswald', sans-serif;"><span class="highlight">THE</span>VAULT</a>
        </div>
        <div class="humberger-menu-cart">
            <ul>
                <li><a href="cart.php"><i class="fa-solid fa-shopping-bag"></i> <span><?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?></span></a></li>
            </ul>
        </div>
        <nav class="humberger-menu-nav mobile-menu">
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin/index.php" style="color: var(--primary); font-weight: 700;">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
                <li class="active"><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <div id="mobile-menu-wrap"></div>
        <div class="header-top-right-social">
            <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#"><i class="fa-brands fa-twitter"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
            <a href="#"><i class="fa-brands fa-pinterest-p"></i></a>
        </div>
        <div class="humberger-menu-contact">
            <ul>
                <li><i class="fa fa-envelope"></i> vault@wheelcollectors.in</li>
                <li>Free Shipping for all Collector Orders</li>
            </ul>
        </div>
    </div>

    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f2f2f2; padding: 10px 0;">
                    <div class="header-top-left" style="display: flex; align-items: center; gap: 30px; font-size: 14px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-envelope"></i> vault@wheelcollectors.in
                        </div>
                        <div>Free Shipping for all Collector Orders</div>
                    </div>
                    <div class="header-top-right" style="display: flex; align-items: center; gap: 30px; font-size: 14px;">
                        <div class="header-top-right-social" style="display: flex; gap: 15px;">
                            <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#"><i class="fa-brands fa-twitter"></i></a>
                            <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                            <a href="#"><i class="fa-brands fa-pinterest-p"></i></a>
                        </div>
                        <div class="header-top-right-auth">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <a href="admin/index.php" style="color: var(--primary); font-weight: 700; margin-right: 15px;"><i class="fa-solid fa-gauge"></i> Admin</a>
                                <?php endif; ?>
                                <a href="logout.php" style="color: var(--text-dark); font-weight: 500;"><i class="fa-solid fa-user"></i> Logout</a>
                            <?php else: ?>
                                <a href="login.php" style="color: var(--text-dark); font-weight: 500;"><i class="fa-solid fa-user"></i> Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" style="margin-top: 10px;">
            <div class="row" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="header-logo">
                    <a href="index.php" style="font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 30px; letter-spacing: -1px; display: flex; align-items: center; color: var(--text-dark);"><span style="color: var(--primary);">WHEEL</span>COLLECTORS</a>
                </div>
                <nav class="header-menu" style="width: 50%; text-align: center;">
                    <ul style="display: flex; justify-content: center; gap: 40px; list-style: none;">
                        <li><a href="index.php" style="font-weight: 700; color: var(--text-dark); text-transform: uppercase; font-size: 14px; letter-spacing: 2px;">Home</a></li>
                        <li><a href="shop.php" style="font-weight: 700; color: var(--text-dark); text-transform: uppercase; font-size: 14px; letter-spacing: 2px;">Shop</a></li>
                        <li><a href="categories.php" style="font-weight: 700; color: var(--text-dark); text-transform: uppercase; font-size: 14px; letter-spacing: 2px;">Categories</a></li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin/index.php" style="font-weight: 700; color: var(--primary); text-transform: uppercase; font-size: 14px; letter-spacing: 2px;">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="contact.php" style="font-weight: 700; color: var(--text-dark); text-transform: uppercase; font-size: 14px; letter-spacing: 2px;">Contact</a></li>
                    </ul>
                </nav>
                <div class="header-cart" style="width: 25%; text-align: right; display: flex; justify-content: flex-end; gap: 20px; align-items: center;">
                    <button class="search-trigger-mobile" onclick="document.querySelector('.search-overlay').style.display='flex'" style="display: none; background: none; border: none; color: var(--text-dark); font-size: 18px; cursor: pointer;">
                        <i class="fa fa-magnifying-glass"></i>
                    </button>
                    <ul style="display: flex; gap: 20px; list-style: none; margin: 0;">
                        <li><a href="cart.php" style="position: relative; color: var(--text-dark); font-size: 18px;">
                            <i class="fa fa-shopping-bag"></i> 
                            <span style="position: absolute; top: -10px; right: -10px; width: 18px; height: 18px; background: var(--primary); color: #fff; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center;">
                                <?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>
                            </span>
                        </a></li>
                    </ul>
                </div>
                <div class="humberger-open">
                    <i class="fa fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <div class="search-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
        <button onclick="document.querySelector('.search-overlay').style.display='none'" style="position: absolute; top: 30px; right: 30px; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        <div class="container" style="max-width: 800px; text-align: center;">
            <h2 style="font-family: 'Orbitron', sans-serif; font-size: 2rem; margin-bottom: 30px;">Access the Vault</h2>
            <form action="shop.php" method="GET" style="position: relative;">
                <input type="text" name="search" placeholder="Search for models, series, or rarities..." autofocus style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 25px 30px; border-radius: 50px; font-size: 1.5rem; color: white; outline: none;">
                <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: var(--primary); border: none; color: white; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; font-size: 1.2rem;"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <p style="margin-top: 20px; color: var(--text-muted);">Try searching for: <span style="color: var(--primary);">Mustang</span>, <span style="color: var(--primary);">Off-Road</span>, or <span style="color: var(--primary);">Premium</span></p>
        </div>
    </div>

    <?php if (isset($_SESSION['toast'])): ?>
    <div id="cart-toast" style="position: fixed; bottom: 30px; right: 30px; background: var(--primary); color: white; padding: 15px 30px; border-radius: 8px; z-index: 3000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 15px; animation: slideIn 0.5s ease-out;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= $_SESSION['toast'] ?></span>
        <?php unset($_SESSION['toast']); ?>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('cart-toast');
            if(toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                toast.style.transition = 'all 0.5s ease-in-out';
                setTimeout(() => toast.remove(), 500);
            }
        }, 3000);
    </script>
    <?php endif; ?>
