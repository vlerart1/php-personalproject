<?php
// admin/bookings.php
$page_title = 'Manage Bookings';
require_once 'includes/header.php';

$message = '';

// Handle Status Updates
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    
    if (in_array($status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $message = "Booking status updated to " . ucfirst($status);
    }
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$id]);
    $message = "Booking deleted successfully!";
}

// Fetch Bookings with detailed info
$query = "
    SELECT b.*, u.first_name, u.last_name, u.email as user_email, h.name as hotel_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN hotels h ON b.hotel_id = h.id 
    ORDER BY b.created_at DESC
";
$bookings = $pdo->query($query)->fetchAll();
?>

<?php if ($message): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $message; ?>", 'success'));</script>
<?php endif; ?>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest Details</th>
                    <th>Hotel & Stay</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $booking): ?>
                    <tr>
                        <td style="font-weight: 500;">#<?php echo $booking['id']; ?></td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['hotel_name']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                <?php echo date('M j', strtotime($booking['check_in_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?>
                            </div>
                        </td>
                        <td style="font-weight: 700; color: var(--sidebar-bg);"><?php echo format_price($booking['total_price']); ?></td>
                        <td><span class="badge <?php echo $booking['status']; ?>"><?php echo $booking['status']; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <div class="dropdown" style="position: relative;">
                                    <button class="btn btn-sm btn-primary" onclick="this.nextElementSibling.classList.toggle('show')">Status <i class="fas fa-chevron-down"></i></button>
                                    <div class="dropdown-content" style="display: none; position: absolute; right: 0; background: #fff; box-shadow: var(--shadow-soft); border-radius: 8px; z-index: 10; border: 1px solid var(--border-color); min-width: 120px; margin-top: 4px;">
                                        <a href="?id=<?php echo $booking['id']; ?>&status=confirmed" style="display: block; padding: 10px 16px; color: #2563eb; font-size: 0.8rem; font-weight: 500;">Confirm</a>
                                        <a href="?id=<?php echo $booking['id']; ?>&status=completed" style="display: block; padding: 10px 16px; color: #059669; font-size: 0.8rem; font-weight: 500;">Complete</a>
                                        <a href="?id=<?php echo $booking['id']; ?>&status=cancelled" style="display: block; padding: 10px 16px; color: #dc2626; font-size: 0.8rem; font-weight: 500;">Cancel</a>
                                    </div>
                                </div>
                                <a href="?delete=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .dropdown-content.show { display: block !important; }
    .dropdown-content a:hover { background: #f9fafb; }
</style>

<script>
    // Close dropdowns when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.btn-primary') && !event.target.matches('.fa-chevron-down')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].classList.contains('show')) {
                    dropdowns[i].classList.remove('show');
                }
            }
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
