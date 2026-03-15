<?php 
    $pageTitle = 'Offers';
    $currentPage = 'offers';
    include 'header.php'; 
?>

    <main class="section">
        <div class="container">
            <h2 class="section-title">Special Offers</h2>
            <?php
            require_once 'db.php';
            $db = get_db_connection();
            // Fetch a random featured product for the promo banner, ensuring category exists
            $promo_stmt = $db->query("SELECT * FROM products WHERE category_id IN (SELECT id FROM categories) ORDER BY RANDOM() LIMIT 1");
            $promo_prod = $promo_stmt->fetch();
            ?>
            <section class="promo" style="margin-bottom: 50px;">
                <div class="promo-content">
                    <div class="promo-text">
                        <h2>Weekend <span class="highlight">Racing</span> Sale</h2>
                        <h3>UP TO 25% OFF!</h3>
                        <p>Get ready for the track with our exclusive weekend discounts on models like the <?= htmlspecialchars($promo_prod['name']) ?>.</p>
                        <a href="product-details.php?id=<?= $promo_prod['id'] ?>" class="btn btn-primary" style="margin-top:20px;">Shop Now</a>
                    </div>
                    <div class="promo-image">
                        <img src="<?= htmlspecialchars($promo_prod['image_url']) ?>" alt="Promo Car">
                    </div>
                </div>
            </section>

            <div class="products-grid">
                <?php
                require_once 'db.php';
                $db = get_db_connection();
                // Fetch products with specific badges, ensuring category exists
                $stmt = $db->query("SELECT * FROM products WHERE badge IN ('NEW', 'FLAGSHIP', 'LIMITED') AND category_id IN (SELECT id FROM categories) ORDER BY RANDOM() LIMIT 10");
                $offer_products = $stmt->fetchAll();

                foreach ($offer_products as $product):
                ?>
                <div class="product-card">
                    <a href="product-details.php?id=<?= $product['id'] ?>" style="display: block; color: inherit; text-decoration: none;">
                        <div class="product-image">
                            <?php if ($product['badge']): ?>
                            <span class="premium-badge"><?= $product['badge'] ?></span>
                            <?php endif; ?>
                            <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
                        </div>
                        <div class="product-info">
                            <h3><?= $product['name'] ?> <small><?= $product['subtitle'] ?></small></h3>
                            <div class="rating">
                                <?php 
                                $stars = round($product['rating']);
                                for($i=0; $i<$stars; $i++) echo '<i class="fa-solid fa-star"></i>';
                                ?>
                                <span>(<?= $product['rating'] ?>)</span>
                            </div>
                            <div class="price">₹ <?= number_format($product['price']) ?></div>
                            <a href="cart_action.php?action=add&id=<?= $product['id'] ?>" class="add-to-cart" style="display: block; text-align: center;">Add to Cart</a>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>
