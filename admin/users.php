<?php
require_once '../db.php';
$db = get_db_connection();
$message = '';

// Handle Delete
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    // Prevent self-deletion
    session_start();
    if ($id == $_SESSION['user_id']) {
        $message = ["error", "Error: You cannot delete your own account!"];
    } else {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $message = ["success", "User deleted successfully!"];
    }
}

// Handle Role Update
if (isset($_POST['toggle_role'])) {
    $id = $_POST['id'];
    $current_role = $_POST['role'];
    $new_role = $current_role == 'admin' ? 'user' : 'admin';
    
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$new_role, $id]);
    $message = ["success", "User role updated successfully!"];
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include 'header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;">User Management</h3>
    </div>
    <div style="padding: 35px;">
        <?php if ($message): ?>
            <?php $msgType = $message[0]; $msgText = $message[1]; ?>
            <div style="background: <?= $msgType == 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(244, 63, 94, 0.1)' ?>; color: <?= $msgType == 'success' ? '#34d399' : '#fb7185' ?>; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid <?= $msgType == 'success' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(244, 63, 94, 0.2)' ?>;">
                <?= $msgText ?>
            </div>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Info</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="color: var(--premium-text-muted); font-weight: 500;">#<?= $user['id'] ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--premium-primary), var(--premium-secondary)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: white; font-size: 1rem;"><?= htmlspecialchars($user['name']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--premium-text-muted);"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= $user['role'] == 'admin' ? 'rgba(99, 102, 241, 0.1)' : 'rgba(148, 163, 184, 0.1)' ?>; color: <?= $user['role'] == 'admin' ? 'var(--premium-primary)' : 'var(--premium-text-muted)' ?>; border: 1px solid <?= $user['role'] == 'admin' ? 'rgba(99, 102, 241, 0.2)' : 'rgba(148, 163, 184, 0.2)' ?>;">
                                <?= strtoupper($user['role']) ?>
                            </span>
                        </td>
                        <td style="color: var(--premium-text-muted); font-size: 0.9rem;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 12px; align-items: center;">
                                <form method="POST" action="users.php" style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="role" value="<?= $user['role'] ?>">
                                    <button type="submit" name="toggle_role" class="btn-sm" title="Toggle Role" style="color: var(--premium-primary); background: rgba(99, 102, 241, 0.05); border: none; cursor: pointer; padding: 10px; border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 1.1rem;">
                                        <i class="fas fa-user-shield"></i>
                                    </button>
                                </form>
                                <form method="POST" action="users.php" style="display: inline-block; margin: 0;">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="button" name="delete_user" class="delete-btn-robust" onclick="handleDeleteRobust(this)" style="color: var(--premium-accent); background: rgba(244, 63, 94, 0.05); border: none; cursor: pointer; padding: 10px; font-size: 1.1rem; border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" title="Delete User">
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
</div>

<?php include 'footer.php'; ?>
