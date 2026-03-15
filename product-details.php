<?php 
    require_once 'db.php';
    $db = get_db_connection();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
    $stmt = $db->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        header("Location: shop.php");
        exit();
    }

    $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
    $stmt->execute([$id]);
    $gallery = $stmt->fetchAll();

    $pageTitle = $product['name'] . ' - Details';
    $currentPage = 'shop';
    include 'header.php'; 
?>

    <main class="section">
        <div class="container">
            <div class="product-details-grid">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="product-gallery-main" id="main-image-container">
                        <?php if ($product['badge']): ?>
                        <div class="product-badge"><?= $product['badge'] ?></div>
                        <?php endif; ?>
                        <img id="main-product-image" src="<?= !empty($gallery) ? $gallery[0]['image_path'] : 'assets/images/placeholder.png' ?>" alt="<?= $product['name'] ?>">
                    </div>
                    <?php if (count($gallery) > 1): ?>
                    <div class="product-thumbnails" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 10px; margin-top: 15px;">
                        <?php foreach ($gallery as $img): ?>
                        <div class="thumbnail" onclick="document.getElementById('main-product-image').src='<?= $img['image_path'] ?>'" style="aspect-ratio: 1/1; background: #f8fafc; border-radius: 8px; border: 1px solid #f2f2f2; cursor: pointer; overflow: hidden; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                            <img src="<?= $img['image_path'] ?>" style="width:100%; height:100%; object-fit:contain;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <style>
                    .thumbnail:hover { border-color: var(--primary) !important; transform: translateY(-2px); }
                </style>

                <!-- Product Content -->
                <div class="product-details-content">
                    <span style="color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem;"><?= $product['cat_name'] ?> Series</span>
                    <h1><?= $product['name'] ?> <small style="display: block; font-size: 1.1rem; color: #666; font-weight: 400; margin-top: 5px;"><?= $product['subtitle'] ?></small></h1>
                    
                    <div class="rating" style="font-size: 1rem; margin-bottom: 20px; color: #edbb0e;">
                        <?php 
                        $full_stars = floor($product['rating']);
                        for($i=0; $i<$full_stars; $i++) echo '<i class="fa-solid fa-star"></i>';
                        if(($product['rating'] - $full_stars) >= 0.5) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                        ?>
                        <span style="color: #666; margin-left:10px;">(<?= $product['review_count'] ?> Reviews)</span>
                    </div>

                    <div class="price">₹ <?= number_format($product['price']) ?></div>

                    <p><?= $product['description'] ?></p>

                    <form action="cart_action.php" method="GET" style="display: flex; gap: 15px; margin-bottom: 40px;">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <div style="width: 120px; display: flex; align-items: center; border: 1px solid #ebebeb; border-radius: 4px; overflow: hidden;">
                            <button type="button" onclick="const input = this.parentElement.querySelector('input[name=qty]'); if(input.value > 1) input.value--" style="flex: 1; background: #f5f5f5; border: none; padding: 10px; cursor: pointer;">-</button>
                            <input type="number" name="qty" value="1" min="1" style="width: 40px; text-align: center; border: none; font-weight: 700;">
                            <button type="button" onclick="const input = this.parentElement.querySelector('input[name=qty]'); input.value++" style="flex: 1; background: #f5f5f5; border: none; padding: 10px; cursor: pointer;">+</button>
                        </div>
                        <button type="submit" class="site-btn" style="flex: 1; background: var(--primary); color: white; border: none; font-weight: 700; text-transform: uppercase; cursor: pointer; padding: 0 30px;">ADD TO CART</button>
                    </form>

                    <div style="border-top: 1px solid #ebebeb; padding-top: 30px;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                            <i class="fa-solid fa-truck-fast" style="color: var(--primary);"></i>
                            <span style="font-size: 14px; font-weight: 600;">Free Shipping for Collector Orders</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fa-solid fa-shield-check" style="color: var(--primary);"></i>
                            <span style="font-size: 14px; font-weight: 600;">Authenticity Guaranteed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div style="margin-top: 100px;">
                <h2 class="section-title">Related Toy Cars</h2>
                <div class="products-grid">
                    <?php
                    $related_stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND category_id IN (SELECT id FROM categories) LIMIT 4");
                    $related_stmt->execute([$product['category_id'], $product['id']]);
                    $related = $related_stmt->fetchAll();
                    foreach ($related as $product):
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['badge']): ?>
                                <div class="product-badge"><?= $product['badge'] ?></div>
                            <?php endif; ?>
                            <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
                        </div>
                        <div class="product-info">
                            <a href="product-details.php?id=<?= $product['id'] ?>" style="text-decoration: none;"><h3><?= $product['name'] ?></h3></a>
                            <div class="price">₹ <?= number_format($product['price']) ?></div>
                            <a href="cart_action.php?action=add&id=<?= $product['id'] ?>" class="add-to-cart">Add to Cart</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>
