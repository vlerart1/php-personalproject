<?php
// admin/settings.php
$page_title = 'System Settings';
require_once 'includes/header.php';

$message = '';
$error = '';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        
        $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        if (password_verify($current_pass, $admin['password_hash'])) {
            if ($new_pass === $confirm_pass) {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hash, $_SESSION['admin_id']]);
                $message = "Password updated successfully!";
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<?php if ($message): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $message; ?>", 'success'));</script>
<?php endif; ?>
<?php if ($error): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $error; ?>", 'error'));</script>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
    <div class="card">
        <div class="card-header">
            <h2>Change Password</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="update_password" value="1">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Site Information</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Platform Name</label>
                <input type="text" class="form-control" value="ArtsHaven" readonly>
            </div>
            <div class="form-group">
                <label>Admin Email</label>
                <input type="text" class="form-control" value="admin@artshaven.com" readonly>
            </div>
            <div class="form-group">
                <label>System Version</label>
                <input type="text" class="form-control" value="v2.1.0-production" readonly>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px dashed var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
                <i class="fas fa-info-circle"></i> Site-wide settings like maintenance mode and SEO tags can be configured in the config files.
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
