<?php
require_once 'includes/header.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$hotel_id = (int)$_POST['hotel_id'];
$room_type_id = (int)$_POST['room_type_id'];
$check_in = sanitize($_POST['check_in']);
$check_out = sanitize($_POST['check_out']);
$total_price = (float)$_POST['total_price'];
$special_requests = sanitize($_POST['special_requests'] ?? '');

$error = '';

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, hotel_id, check_in_date, check_out_date, total_price, status) 
        VALUES (?, ?, ?, ?, ?, 'confirmed')
    ");
    $stmt->execute([$user_id, $hotel_id, $check_in, $check_out, $total_price]);
    $booking_id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("
        INSERT INTO booking_rooms (booking_id, room_type_id, quantity) 
        VALUES (?, ?, 1)
    ");
    $stmt2->execute([$booking_id, $room_type_id]);

    $pdo->commit();
    redirect("booking-success.php?id=$booking_id");

} catch (Exception $e) {
    $pdo->rollBack();
    $error = "Failed to process booking. Please try again.";
}
?>

<div class="container text-center fade-in" style="margin-top: 100px; min-height: 50vh;">
    <?php if ($error): ?>
        <div class="glass-card" style="max-width: 500px; margin: 0 auto; border-top: 4px solid var(--danger);">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 16px;"><i class="fas fa-exclamation-triangle"></i></div>
            <h2 style="margin-bottom: 16px;">Booking Failed</h2>
            <p style="color: var(--gray-light); margin-bottom: 24px;"><?php echo htmlspecialchars($error); ?></p>
            <a href="index.php" class="btn w-100">Return to Home</a>
        </div>
    <?php endif; ?>
</div>

<script>document.querySelector('.fade-in').classList.add('visible');</script>
<?php require_once 'includes/footer.php'; ?>
