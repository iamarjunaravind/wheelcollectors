<?php 
    $pageTitle = 'Shop';
    $currentPage = 'shop';
    include 'header.php'; 
    require_once 'db.php';
    $db = get_db_connection();

    $category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $query = "SELECT * FROM products WHERE 1=1 AND category_id IN (SELECT id FROM categories)";
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

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $cat_name = "All RC Cars";
    if ($search) {
        $cat_name = "Search results for '$search'";
    } elseif ($category_id) {
        $cat_stmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
        $cat_stmt->execute([$category_id]);
        $cat = $cat_stmt->fetch();
        if ($cat) $cat_name = $cat['name'] . " Series";
    }
?>

    <main class="section">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 60px;">
                <h2 class="section-title" style="margin-bottom: 0;"><?= $cat_name ?></h2>
                <?php if ($category_id || $search): ?>
                <a href="shop.php" class="btn btn-outline" style="padding: 10px 20px; font-size: 0.9rem;"><i class="fa-solid fa-xmark"></i> Clear Filters</a>
                <?php endif; ?>
            </div>
            <div class="products-grid">
                <!-- Drift Cars -->
                <?php foreach ($products as $product): ?>
                <div class="product-card" onclick="window.location.href='product-details.php?id=<?= $product['id'] ?>'" style="cursor: pointer;">
                    <a href="product-details.php?id=<?= $product['id'] ?>">
                        <?php if ($product['badge']): ?>
                        <span class="premium-badge"><?= $product['badge'] ?></span>
                        <?php endif; ?>
                        <div class="product-image">
                            <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
                        </div>
                    </a>
                    <div class="product-info">
                        <a href="product-details.php?id=<?= $product['id'] ?>"><h3><?= $product['name'] ?> <small><?= $product['subtitle'] ?></small></h3></a>
                        <div class="rating">
                            <?php 
                            $full_stars = floor($product['rating']);
                            $has_half = ($product['rating'] - $full_stars) >= 0.5;
                            for($i=0; $i<$full_stars; $i++) echo '<i class="fa-solid fa-star"></i>';
                            if($has_half) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                            ?>
                            <span>(<?= number_format($product['rating'], 1) ?>)</span>
                        </div>
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
    </main>

<?php include 'footer.php'; ?>
