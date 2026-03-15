<?php 
    $pageTitle = 'Home';
    $currentPage = 'home';
    include 'header.php'; 
    require_once 'db.php';
    $db = get_db_connection();
?>

    <main>
        <section class="hero">
            <div class="container">
                <div class="row" style="display: flex; gap: 30px;">
                    <div class="col-lg-3" style="width: 25%;">
                        <div class="hero-sidebar">
                            <div class="hero-sidebar-title">
                                <i class="fa fa-bars"></i>
                                <span>All Categories</span>
                            </div>
                            <ul>
                                <?php
                                $hero_cats = $db->query("SELECT * FROM categories LIMIT 11")->fetchAll();
                                foreach ($hero_cats as $cat):
                                ?>
                                <li><a href="shop.php?category=<?= $cat['id'] ?>"><?= $cat['name'] ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-9" style="width: 75%;">
                        <div class="hero-search" style="display: flex; align-items: center; gap: 30px; margin-bottom: 30px;">
                            <div class="hero-search-form" style="flex: 1;">
                                <form action="shop.php" method="GET" style="display: flex; width: 100%;">
                                    <input type="text" name="search" placeholder="What model do you need?" style="flex: 1; border: 1px solid #ebebeb; padding: 0 20px; font-size: 16px; outline: none; height: 50px;">
                                    <button type="submit" class="site-btn" style="background: var(--primary); color: white; border: none; padding: 0 30px; font-weight: 700; text-transform: uppercase; cursor: pointer;">SEARCH</button>
                                </form>
                            </div>
                            <div class="hero-search-phone" style="display: flex; align-items: center; gap: 15px;">
                                <div class="hero-search-phone-icon" style="width: 50px; height: 50px; background: #f5f5f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 18px;">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <div class="hero-search-phone-text">
                                    <h5 style="font-size: 18px; color: #1c1c1c; font-weight: 700; margin-bottom: 0;">+65 11.188.888</h5>
                                    <span style="font-size: 12px; color: var(--text-muted);">support 24/7 time</span>
                                </div>
                            </div>
                        </div>
                        <div class="hero-slider">
                            <?php
                            // Use featured products or latest products for the hero slider
                            $hero_products = $db->query("SELECT * FROM products WHERE is_featured = 1 AND category_id IN (SELECT id FROM categories) ORDER BY RANDOM() LIMIT 3")->fetchAll();
                            if (empty($hero_products)) {
                                $hero_products = $db->query("SELECT * FROM products WHERE category_id IN (SELECT id FROM categories) ORDER BY id DESC LIMIT 3")->fetchAll();
                            }
                            
                            $hero_texts = [
                                ['span' => 'PROJECT XPORTS', 'h2' => 'Engineered For <br />Extreme Performance', 'p' => 'Professional Grade RC Machines', 'btn' => 'GO FAST'],
                                ['span' => 'DRIFT KINGS', 'h2' => 'Master The Angle <br />Defy Everything', 'p' => 'Scale Drifting Excellence', 'btn' => 'START DRIFTING'],
                                ['span' => 'BASHING PROS', 'h2' => 'Jump Higher <br />Land Stronger', 'p' => 'Indestructible Off-Road Power', 'btn' => 'BASH NOW']
                            ];

                            foreach ($hero_products as $idx => $hp):
                                $text = $hero_texts[$idx % 3];
                            ?>
                            <div class="hero-slide <?= $idx === 0 ? 'active' : '' ?>" style="background-image: url('<?= htmlspecialchars($hp['image_url']) ?>'); background-size: contain; background-repeat: no-repeat; background-position: center; background-color: #f8fafc;">
                                <div class="hero-banner-text">
                                    <span><?= $text['span'] ?></span>
                                    <h2><?= htmlspecialchars($hp['name']) ?></h2>
                                    <p><?= htmlspecialchars($hp['subtitle']) ?></p>
                                    <a href="product-details.php?id=<?= $hp['id'] ?>" class="btn btn-primary"><span><?= $text['btn'] ?></span></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div class="hero-slider-dots"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php
        // Dynamically fetch all categories for showcases
        $showcase_categories = $db->query("SELECT * FROM categories")->fetchAll();

        foreach ($showcase_categories as $cat):
            $cat_products = $db->prepare("SELECT * FROM products WHERE category_id = ? LIMIT 8");
            $cat_products->execute([$cat['id']]);
            $products = $cat_products->fetchAll();
            
            if (empty($products)) continue; // Skip empty categories
        ?>
        <section class="section category-showcase">
            <div class="container">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: baseline;">
                    <h2 class="section-title"><?= htmlspecialchars($cat['name']) ?></h2>
                    <a href="shop.php?category=<?= $cat['id'] ?>" class="view-all" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; text-decoration: none;">View All <i class="fa fa-arrow-right" style="font-size: 10px; margin-left: 5px;"></i></a>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="window.location.href='product-details.php?id=<?= $product['id'] ?>'" style="cursor: pointer;">
                        <div class="product-image">
                            <?php if ($product['badge']): ?>
                                <div class="product-badge"><?= htmlspecialchars($product['badge']) ?></div>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="product-info">
                            <a href="product-details.php?id=<?= $product['id'] ?>" style="text-decoration: none;"><h3><?= htmlspecialchars($product['name']) ?></h3></a>
                            <div class="product-actions">
                                <div class="price">₹ <?= number_format($product['price']) ?></div>
                                <form action="cart_action.php" method="POST" style="display:inline;" onclick="event.stopPropagation();">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" class="btn-cart">
                                        <i class="fa fa-shopping-cart"></i>
                                        <span>ADD TO CART</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endforeach; ?>

        <!-- Featured Products Slider -->
        <section class="section featured-products">
            <div class="container">
                <div class="section-header" style="margin-bottom: 20px; text-align: left;">
                    <h2 class="section-title">Featured Collections</h2>
                    <p style="color: var(--text-muted); text-align: left; margin-top: 5px;">Handpicked favorites for the ultimate enthusiast</p>
                </div>
                
                <div class="product-slider-container">
                    <?php
                    // Fetch featured products dynamically, ensuring category exists
                    $featured = $db->query("SELECT * FROM products WHERE is_featured = 1 AND category_id IN (SELECT id FROM categories) LIMIT 12")->fetchAll();
                    if (empty($featured)) {
                        $featured = $db->query("SELECT * FROM products WHERE category_id IN (SELECT id FROM categories) ORDER BY RANDOM() LIMIT 12")->fetchAll();
                    }
                    foreach ($featured as $product):
                    ?>
                    <div class="product-card slider-card" onclick="window.location.href='product-details.php?id=<?= $product['id'] ?>'">
                        <div class="product-image">
                            <?php if ($product['badge']): ?>
                                <div class="product-badge"><?= htmlspecialchars($product['badge']) ?></div>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="product-info">
                            <a href="product-details.php?id=<?= $product['id'] ?>" style="text-decoration: none;"><h3><?= htmlspecialchars($product['name']) ?></h3></a>
                            <div class="product-actions">
                                <div class="price">₹ <?= number_format($product['price']) ?></div>
                                <form action="cart_action.php" method="POST" style="display:inline;" onclick="event.stopPropagation();">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" class="btn-cart">
                                        <i class="fa fa-shopping-cart"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    </main>

<?php include 'footer.php'; ?>
