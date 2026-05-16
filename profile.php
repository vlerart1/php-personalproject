<?php
// profile.php
require_once 'includes/header.php';

if (!is_logged_in()) {
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch booking history
$booking_stmt = $pdo->prepare("
    SELECT b.*, h.name as hotel_name, h.city 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$booking_stmt->execute([$user_id]);
$bookings = $booking_stmt->fetchAll();

?>

<div class="container profile-page">
    <div class="profile-header glass-card">
        <div class="user-info">
            <h2>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
            <p>Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <div class="profile-content">
        <div class="bookings-section glass-card">
            <h3>Your Bookings</h3>
            
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Hotel</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Total Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td>
                                        <a href="hotel-details.php?id=<?php echo $booking['hotel_id']; ?>">
                                            <?php echo htmlspecialchars($booking['hotel_name']); ?>
                                        </a><br>
                                        <small><?php echo htmlspecialchars($booking['city']); ?></small>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?></td>
                                    <td><?php echo format_price($booking['total_price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>You have no bookings yet. <a href="hotels.php">Browse our hotels</a> to plan your next trip!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.profile-page {
    margin-top: 2rem;
}
.profile-header {
    margin-bottom: 2rem;
    padding: 2rem;
}
.profile-header h2 {
    color: var(--accent-color);
    margin-bottom: 1rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.data-table th, .data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--glass-border);
}
.data-table th {
    background: rgba(255,255,255,0.05);
    font-weight: 600;
}

.status-badge {
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
.status-pending { background: #f39c12; color: #fff; }
.status-confirmed { background: #3498db; color: #fff; }
.status-checked_in { background: #9b59b6; color: #fff; }
.status-completed { background: #2ecc71; color: #fff; }
.status-cancelled { background: #e74c3c; color: #fff; }

.table-responsive {
    overflow-x: auto;
}
</style>

<?php require_once 'includes/footer.php'; ?>
