<?php
// admin/users.php
$page_title = 'Manage Users';
require_once 'includes/header.php';

$message = '';

// Handle Status Toggle (Block/Unblock)
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $current_status = $_GET['toggle_status'];
    $new_status = $current_status === 'active' ? 'blocked' : 'active';
    
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    $message = "User has been " . ($new_status === 'blocked' ? 'blocked' : 'unblocked');
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    $message = "User deleted successfully!";
}

// Fetch Users with booking count
$query = "
    SELECT u.*, COUNT(b.id) as booking_count 
    FROM users u 
    LEFT JOIN bookings b ON u.id = b.user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
";
$users = $pdo->query($query)->fetchAll();
?>

<?php if ($message): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $message; ?>", 'success'));</script>
<?php endif; ?>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email & Phone</th>
                    <th>Bookings</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--sidebar-bg);">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                </div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></div>
                        </td>
                        <td><div class="badge" style="background: #eff6ff; color: #3b82f6;"><?php echo $user['booking_count']; ?> Bookings</div></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td><span class="badge <?php echo $user['status']; ?>"><?php echo $user['status']; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="?id=<?php echo $user['id']; ?>&toggle_status=<?php echo $user['status']; ?>" class="btn btn-sm <?php echo $user['status'] === 'active' ? 'btn-danger' : 'btn-primary'; ?>" title="<?php echo $user['status'] === 'active' ? 'Block' : 'Unblock'; ?>">
                                    <i class="fas fa-<?php echo $user['status'] === 'active' ? 'user-slash' : 'user-check'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? All their bookings will be lost!')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
