<?php
require_once '../db.php';
$db = get_db_connection();
$message = '';

// Handle Delete
if (isset($_POST['delete_category'])) {
    $id = $_POST['id'];
    try {
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $message = ["success", "Category deleted successfully!"];
    } catch (PDOException $e) {
        $message = ["error", "Error deleting category: " . $e->getMessage()];
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $slug = $_POST['slug'] ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $image_url = $_POST['image_url'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $image_url, $_POST['id']]);
        $message = ["success", "Category updated successfully!"];
    } else {
        // Add
        $stmt = $db->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $image_url]);
        $message = ["success", "Category added successfully!"];
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();

$edit_cat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_cat = $stmt->fetch();
}

include 'header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;"><?= $edit_cat ? 'Edit Category' : 'Add New Category' ?></h3>
    </div>
    <div style="padding: 35px;">
        <?php if ($message): ?>
            <?php $msgType = $message[0]; $msgText = $message[1]; ?>
            <div style="background: <?= $msgType == 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(244, 63, 94, 0.1)' ?>; color: <?= $msgType == 'success' ? '#34d399' : '#fb7185' ?>; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid <?= $msgType == 'success' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(244, 63, 94, 0.2)' ?>;">
                <?= $msgText ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="categories.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end;">
            <?php if ($edit_cat): ?>
                <input type="hidden" name="id" value="<?= $edit_cat['id'] ?>">
            <?php endif; ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Category Name</label>
                <input type="text" name="name" value="<?= $edit_cat ? htmlspecialchars($edit_cat['name']) : '' ?>" required style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--premium-primary)'" onblur="this.style.borderColor='var(--premium-border)'">
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Slug (Optional)</label>
                <input type="text" name="slug" value="<?= $edit_cat ? htmlspecialchars($edit_cat['slug']) : '' ?>" placeholder="auto-generated" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: var(--premium-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Image URL</label>
                <input type="text" name="image_url" value="<?= $edit_cat ? htmlspecialchars($edit_cat['image_url']) : '' ?>" style="padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--premium-border); border-radius: 10px; color: white; outline: none;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 12px; justify-content: center;"><?= $edit_cat ? 'Update' : 'Add Category' ?></button>
                <?php if ($edit_cat): ?>
                    <a href="categories.php" class="btn" style="flex: 1; background: rgba(255,255,255,0.05); text-align: center; justify-content: center; color: white; text-decoration: none;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;">All Categories</h3>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Slug</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="color: var(--premium-text-muted); font-weight: 500;">#<?= $cat['id'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="../<?= htmlspecialchars($cat['image_url']) ?>" alt="" style="width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 1px solid var(--premium-border); background: var(--premium-bg);">
                            <span style="font-weight: 700; color: white; font-size: 1rem;"><?= htmlspecialchars($cat['name']) ?></span>
                        </div>
                    </td>
                    <td><code style="background: rgba(255,255,255,0.05); color: var(--premium-primary); padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid var(--premium-border);"><?= htmlspecialchars($cat['slug']) ?></code></td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
                            <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm" style="color: var(--premium-primary); font-size: 1.1rem; background: rgba(99, 102, 241, 0.05); padding: 8px; border-radius: 8px; width: 36px; height: 36px; justify-content: center;" title="Edit Category"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="categories.php" style="display: inline-block; margin: 0;">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="button" name="delete_category" class="delete-btn-robust" onclick="handleDeleteRobust(this)" style="color: var(--premium-accent); background: rgba(244, 63, 94, 0.05); border: none; cursor: pointer; padding: 8px; font-size: 1.1rem; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" title="Delete Category">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
