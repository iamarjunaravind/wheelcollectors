<?php
require_once '../db.php';
$db = get_db_connection();
$message = '';

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $id = $_POST['id'];
    try {
        $db->beginTransaction();
        // Delete images from disk first (optional but good practice)
        $stmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $imgs = $stmt->fetchAll();
        foreach($imgs as $img) {
            $path = '../' . $img['image_path'];
            if(file_exists($path) && strpos($path, 'assets/images/products/') !== false) {
                unlink($path);
            }
        }
        
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $db->commit();
        $message = ["success", "Product and its images deleted successfully!"];
    } catch (PDOException $e) {
        $db->rollBack();
        $message = ["error", "Error deleting product: " . $e->getMessage()];
    }
}

// Handle Delete Single Image
if (isset($_POST['delete_image'])) {
    $img_id = $_POST['image_id'];
    $prod_id = $_POST['id'];
    try {
        $stmt = $db->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->execute([$img_id, $prod_id]);
        $img = $stmt->fetch();
        if ($img) {
            $path = '../' . $img['image_path'];
            if(file_exists($path) && strpos($path, 'assets/images/products/') !== false) {
                unlink($path);
            }
            $db->prepare("DELETE FROM product_images WHERE id = ?")->execute([$img_id]);
            $message = ["success", "Image removed successfully!"];
        }
    } catch (PDOException $e) {
        $message = ["error", "Error removing image: " . $e->getMessage()];
    }
    // Stay on edit page
    header("Location: products.php?edit=" . $prod_id);
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $subtitle = $_POST['subtitle'];
    $price = $_POST['price'];
    $rating = $_POST['rating'] ?: 0.0;
    $badge = $_POST['badge'] ?: null;
    $description = $_POST['description'];
    $stock = $_POST['stock'] ?: 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // We'll update the main image_url to the first uploaded image or keep current
    $current_id = isset($_POST['id']) ? $_POST['id'] : null;

    try {
        $db->beginTransaction();

        if ($current_id) {
            // Update
            $stmt = $db->prepare("UPDATE products SET category_id = ?, name = ?, subtitle = ?, price = ?, rating = ?, badge = ?, description = ?, stock = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$category_id, $name, $subtitle, $price, $rating, $badge, $description, $stock, $is_featured, $current_id]);
            $product_id = $current_id;
            $message = ["success", "Product updated successfully!"];
        } else {
            // Add
            $stmt = $db->prepare("INSERT INTO products (category_id, name, subtitle, price, rating, badge, description, stock, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $subtitle, $price, $rating, $badge, $description, $stock, $is_featured]);
            $product_id = $db->lastInsertId();
            $message = ["success", "Product added successfully!"];
        }

        // Handle File Uploads
        if (!empty($_FILES['product_images']['name'][0])) {
            $upload_dir = '../assets/images/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            foreach ($_FILES['product_images']['name'] as $key => $val) {
                if ($_FILES['product_images']['error'][$key] == 0) {
                    $tmp_name = $_FILES['product_images']['tmp_name'][$key];
                    $ext = pathinfo($val, PATHINFO_EXTENSION);
                    $new_name = "prod_" . $product_id . "_" . time() . "_" . $key . "." . $ext;
                    $target_file = $upload_dir . $new_name;
                    $db_path = "assets/images/products/" . $new_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        // Check if it's the first image to make primary if no primary exists
                        $has_primary = $db->query("SELECT COUNT(*) FROM product_images WHERE product_id = $product_id AND is_primary = 1")->fetchColumn();
                        $is_primary = ($has_primary == 0 && $key == 0) ? 1 : 0;
                        
                        $db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)")
                           ->execute([$product_id, $db_path, $is_primary]);
                    }
                }
            }
        }
        
        // Sync the main 'image_url' in products table for backward compatibility
        $primary_img = $db->query("SELECT image_path FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC, id ASC LIMIT 1")->fetchColumn();
        if ($primary_img) {
            $db->prepare("UPDATE products SET image_url = ? WHERE id = ?")->execute([$primary_img, $product_id]);
        }

        $db->commit();
        
        if ($current_id) {
            header("Location: products.php?edit=" . $current_id . "&status=success");
            exit;
        }
    } catch (Exception $e) {
        $db->rollBack();
        $message = ["error", "Error saving product: " . $e->getMessage()];
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $message = ["success", "Changes saved successfully!"];
}

// Search and Filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cat_filter = isset($_GET['cat']) ? $_GET['cat'] : '';

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cat_filter) {
    $query .= " AND p.category_id = ?";
    $params[] = $cat_filter;
}

$query .= " ORDER BY p.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$edit_prod = null;
$product_images = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_prod = $stmt->fetch();
    
    if ($edit_prod) {
        $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
        $stmt->execute([$edit_prod['id']]);
        $product_images = $stmt->fetchAll();
    }
}

include 'header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;"><?= $edit_prod ? 'Edit Product' : 'Add New Product' ?></h3>
    </div>
    <div style="padding: 35px;">
        <?php if ($message): ?>
            <?php $msgType = $message[0]; $msgText = $message[1]; ?>
            <div style="background: <?= $msgType == 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(244, 63, 94, 0.1)' ?>; color: <?= $msgType == 'success' ? '#34d399' : '#fb7185' ?>; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid <?= $msgType == 'success' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(244, 63, 94, 0.2)' ?>;">
                <?= $msgText ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="products.php" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px;">
            <?php if ($edit_prod): ?>
                <input type="hidden" name="id" value="<?= $edit_prod['id'] ?>">
            <?php endif; ?>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Product Name</label>
                <input type="text" name="name" value="<?= $edit_prod ? htmlspecialchars($edit_prod['name']) : '' ?>" required style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--premium-primary)'" onblur="this.style.borderColor='var(--premium-border)'">
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Category</label>
                <select name="category_id" required style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
                    <option value="" style="background: var(--premium-surface);">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($edit_prod && $edit_prod['category_id'] == $cat['id']) ? 'selected' : '' ?> style="background: var(--premium-surface);"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Subtitle / Model</label>
                <input type="text" name="subtitle" value="<?= $edit_prod ? htmlspecialchars($edit_prod['subtitle']) : '' ?>" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Price (₹)</label>
                <input type="number" name="price" value="<?= $edit_prod ? htmlspecialchars($edit_prod['price']) : '' ?>" required style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Rating (0-5)</label>
                <input type="number" step="0.1" max="5" name="rating" value="<?= $edit_prod ? htmlspecialchars($edit_prod['rating']) : '4.5' ?>" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Badge (NEW, RARE, etc)</label>
                <input type="text" name="badge" value="<?= $edit_prod ? htmlspecialchars($edit_prod['badge']) : '' ?>" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Stock Quantity</label>
                <input type="number" name="stock" value="<?= $edit_prod ? htmlspecialchars($edit_prod['stock']) : '50' ?>" required style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; grid-column: 1 / -1;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Product Images (Max 10)</label>
                
                <div id="image-upload-wrapper" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-bottom: 10px;">
                    <?php foreach ($product_images as $img): ?>
                    <div style="position: relative; aspect-ratio: 1; border-radius: 12px; border: 1px solid var(--premium-border); background: white; padding: 5px; overflow: hidden;">
                        <img src="../<?= htmlspecialchars($img['image_path']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                        <button type="button" class="delete-btn-robust" onclick="deleteImage(<?= $img['id'] ?>, <?= $edit_prod['id'] ?>)" style="position: absolute; top: 5px; right: 5px; width: 24px; height: 24px; border-radius: 6px; background: rgba(244, 63, 94, 0.9); color: white; border: none; cursor: pointer; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php if($img['is_primary']): ?>
                        <div style="position: absolute; bottom: 5px; left: 5px; background: var(--premium-primary); color: white; font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; font-weight: 700;">PRIMARY</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <label id="upload-placeholder" style="aspect-ratio: 1; border: 2px dashed var(--premium-border); border-radius: 12px; display: <?= count($product_images) >= 10 ? 'none' : 'flex' ?>; align-items: center; justify-content: center; flex-direction: column; cursor: pointer; transition: all 0.3s; color: var(--premium-text-muted);" onmouseover="this.style.borderColor='var(--premium-primary)'; this.style.color='white'" onmouseout="this.style.borderColor='var(--premium-border)'; this.style.color='var(--premium-text-muted)'">
                        <i class="fas fa-plus-circle" style="font-size: 1.5rem; margin-bottom: 8px;"></i>
                        <span style="font-size: 0.7rem; font-weight: 600;">Add Images</span>
                        <input type="file" name="product_images[]" multiple accept="image/*" onchange="previewImages(this)" style="display: none;">
                    </label>
                </div>
                
                <div id="new-previews" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px;"></div>
                
                <script>
                    function previewImages(input) {
                        const previewContainer = document.getElementById('new-previews');
                        const placeholder = document.getElementById('upload-placeholder');
                        const existingCount = <?= count($product_images) ?>;
                        const remaining = 10 - existingCount;
                        
                        previewContainer.innerHTML = '';
                        
                        if (input.files && input.files.length > 0) {
                            const files = Array.from(input.files).slice(0, remaining);
                            
                            files.forEach((file, index) => {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const div = document.createElement('div');
                                    div.style.cssText = "position: relative; aspect-ratio: 1; border-radius: 12px; border: 1px solid var(--premium-primary); background: rgba(99, 102, 241, 0.1); padding: 5px; overflow: hidden; animation: fadeIn 0.3s ease-out;";
                                    div.innerHTML = `
                                        <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: contain;">
                                        <div style="position: absolute; top: 5px; left: 5px; background: #fbbf24; color: #78350f; font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; font-weight: 700;">NEW</div>
                                    `;
                                    previewContainer.appendChild(div);
                                }
                                reader.readAsDataURL(file);
                            });
                            
                            if (input.files.length > remaining) {
                                alert(`You can only add ${remaining} more images. Only the first ${remaining} have been selected.`);
                            }
                        }
                    }
                </script>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; grid-column: 1 / -1;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Description</label>
                <textarea name="description" rows="3" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none; resize: none;"><?= $edit_prod ? htmlspecialchars($edit_prod['description']) : '' ?></textarea>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <input type="checkbox" name="is_featured" id="is_featured" <?= ($edit_prod && $edit_prod['is_featured']) ? 'checked' : '' ?> style="accent-color: var(--premium-primary); width: 20px; height: 20px;">
                <label for="is_featured" style="font-size: 0.9rem; font-weight: 500; color: var(--premium-text);">Featured Product</label>
            </div>

            <div style="grid-column: 1 / -1; display: flex; gap: 15px; margin-top: 15px;">
                <button type="submit" name="save_product" class="btn btn-primary" style="padding: 14px 30px;"><?= $edit_prod ? 'Update Product' : 'Add Product' ?></button>
                <?php if ($edit_prod): ?>
                    <a href="products.php" class="btn" style="background: rgba(255,255,255,0.05); padding: 14px 30px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header" style="flex-direction: column; align-items: flex-start; gap: 20px; padding: 30px 35px;">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;">Product Inventory</h3>
        <form method="GET" action="products.php" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 200px;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--premium-text-muted);"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or description..." style="width: 100%; padding: 12px 15px 12px 45px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 12px; color: white; outline: none;">
            </div>
            <select name="cat" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 12px; color: white; outline: none; min-width: 180px;">
                <option value="" style="background: var(--premium-surface);">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat_filter == $cat['id'] ? 'selected' : '' ?> style="background: var(--premium-surface);"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px;">Filter</button>
                <?php if ($search || $cat_filter): ?>
                    <a href="products.php" class="btn" style="background: rgba(255,255,255,0.05); padding: 12px 25px; text-decoration: none; color: white;">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Badges</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $prod): ?>
                <tr>
                    <td style="font-weight: 500; color: var(--premium-text-muted);">#<?= $prod['id'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="../<?= htmlspecialchars($prod['image_url']) ?>" alt="" style="width: 50px; height: 50px; object-fit: contain; border-radius: 10px; background: white; padding: 5px; border: 1px solid var(--premium-border);">
                            <div>
                                <div style="font-weight: 700; color: white; font-size: 1rem;"><?= htmlspecialchars($prod['name']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--premium-text-muted);"><?= htmlspecialchars($prod['subtitle']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($prod['category_name']): ?>
                            <span style="background: rgba(99, 102, 241, 0.1); color: var(--premium-primary); padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($prod['category_name']) ?></span>
                        <?php else: ?>
                            <span style="background: rgba(244, 63, 94, 0.1); color: var(--premium-accent); padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; border: 1px solid rgba(244, 63, 94, 0.2);"><i class="fas fa-exclamation-triangle"></i> NO CATEGORY</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: 700; color: white; font-size: 1rem;">₹<?= number_format($prod['price']) ?></td>
                    <td>
                        <div style="font-weight: 600; color: <?= $prod['stock'] < 10 ? '#fb7185' : '#34d399' ?>;">
                            <?= $prod['stock'] ?> Units
                            <?php if ($prod['stock'] < 10): ?>
                                <div style="font-size: 0.7rem; text-transform: uppercase; color: #fb7185; opacity: 0.8;">Low Stock</div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                            <?php if ($prod['is_featured']): ?>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--premium-primary); border: 1px solid rgba(99, 102, 241, 0.2);">FEATURED</span>
                            <?php endif; ?>
                            <?php if ($prod['badge']): ?>
                                <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: white; border: 1px solid var(--premium-border);"><?= strtoupper($prod['badge']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
                            <a href="products.php?edit=<?= $prod['id'] ?>" class="btn btn-sm" style="color: var(--premium-primary); font-size: 1.2rem; background: rgba(99, 102, 241, 0.05); padding: 8px; border-radius: 8px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;" title="Edit Product"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="products.php" style="display: inline-block; margin: 0;">
                                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                <button type="button" name="delete_product" class="delete-btn-robust" onclick="handleDeleteRobust(this)" style="color: var(--premium-accent); background: rgba(244, 63, 94, 0.05); border: none; cursor: pointer; padding: 8px; font-size: 1.2rem; border-radius: 8px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" title="Delete Product">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--premium-text-muted); padding: 50px; font-style: italic;">No products found matching your criteria.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<form id="delete-image-form" method="POST" action="products.php" style="display:none;">
    <input type="hidden" name="id" id="delete-image-prod-id">
    <input type="hidden" name="image_id" id="delete-image-id">
    <input type="hidden" name="delete_image" value="1">
</form>

<script>
function deleteImage(imgId, prodId) {
    if (confirm('Remove this image?')) {
        document.getElementById('delete-image-prod-id').value = prodId;
        document.getElementById('delete-image-id').value = imgId;
        document.getElementById('delete-image-form').submit();
    }
}
</script>

<?php include 'footer.php'; ?>
