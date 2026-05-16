<?php
// admin/reviews.php
$page_title = 'Manage Reviews';
require_once 'includes/header.php';

$message = '';

// Handle Status Updates
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    if (in_array($status, ['approved', 'rejected', 'pending'])) {
        $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $message = "Review status updated to " . ucfirst($status);
    }
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
    $message = "Review deleted successfully!";
}

// Fetch Reviews
$query = "
    SELECT r.*, u.first_name, u.last_name, h.name as hotel_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN hotels h ON r.hotel_id = h.id 
    ORDER BY r.created_at DESC
";
$reviews = $pdo->query($query)->fetchAll();
?>

<?php if ($message): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $message; ?>", 'success'));</script>
<?php endif; ?>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Hotel</th>
                    <th>Rating & Comment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reviews as $review): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></div>
                        </td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($review['hotel_name']); ?></td>
                        <td style="max-width: 300px;">
                            <div style="color: var(--accent); margin-bottom: 4px;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-main); line-height: 1.4;"><?php echo htmlspecialchars($review['comment']); ?></p>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                        <td><span class="badge <?php echo $review['status']; ?>"><?php echo $review['status']; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <?php if ($review['status'] !== 'approved'): ?>
                                    <a href="?id=<?php echo $review['id']; ?>&status=approved" class="btn btn-sm" style="background: #d1fae5; color: #059669;" title="Approve"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                                <?php if ($review['status'] !== 'rejected'): ?>
                                    <a href="?id=<?php echo $review['id']; ?>&status=rejected" class="btn btn-sm" style="background: #fee2e2; color: #dc2626;" title="Reject"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $review['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
