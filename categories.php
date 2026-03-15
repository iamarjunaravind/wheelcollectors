<?php 
    $pageTitle = 'Categories';
    $currentPage = 'categories';
    include 'header.php'; 
    require_once 'db.php';
    $db = get_db_connection();
?>

    <main class="section">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="category-grid">
                <?php
                $categories = $db->query("SELECT * FROM categories")->fetchAll();
                foreach ($categories as $cat):
                ?>
                <a href="shop.php?category=<?= $cat['id'] ?>" class="category-card" style="text-decoration: none; color: inherit; display: block;">
                    <img src="<?= $cat['image_url'] ?>" alt="<?= $cat['name'] ?>">
                    <div class="category-overlay">
                        <h3><?= $cat['name'] ?></h3>
                        <span class="btn-small">Explore</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>
