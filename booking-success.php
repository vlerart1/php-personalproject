<?php
require_once 'includes/header.php';

if (!is_logged_in() || !isset($_GET['id'])) {
    redirect('index.php');
}

$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify booking belongs to user
$stmt = $pdo->prepare("
    SELECT b.*, h.name as hotel_name, h.address, h.city, h.country 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('index.php');
}
?>

<div class="container fade-in" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 60px 24px;">
    
    <div class="glass-card text-center" style="max-width: 600px; width: 100%; padding: 48px; border-top: 4px solid var(--success);">
        
        <div class="success-icon" style="width: 80px; height: 80px; background: rgba(46, 204, 113, 0.1); color: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 24px; animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 style="font-size: 2rem; margin-bottom: 16px;">Booking Confirmed!</h1>
        <p style="color: var(--gray-light); font-size: 1.1rem; margin-bottom: 32px;">Thank you for your reservation. A confirmation email has been sent to you.</p>
        
        <div style="background: rgba(255,255,255,0.03); border-radius: var(--radius-md); padding: 24px; text-align: left; margin-bottom: 32px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 16px;">
                <span style="color: var(--gray-muted);">Booking Reference</span>
                <strong style="color: var(--gold);">#<?php echo $booking_id; ?></strong>
            </div>
            
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 1.1rem; margin-bottom: 4px;"><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                <p style="color: var(--gray-muted); font-size: 0.9rem;"><i class="fas fa-map-marker-alt" style="color: var(--gold); width: 16px;"></i> <?php echo htmlspecialchars($booking['city'] . ', ' . $booking['country']); ?></p>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; background: rgba(255,255,255,0.02); padding: 12px; border-radius: var(--radius-sm);">
                <div>
                    <div style="font-size: 0.85rem; color: var(--gray-muted);">Check-in</div>
                    <div style="font-weight: 500;"><?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.85rem; color: var(--gray-muted);">Check-out</div>
                    <div style="font-weight: 500;"><?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?></div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 16px;">
                <span style="font-size: 1.1rem;">Total Paid</span>
                <strong style="font-size: 1.25rem; color: var(--gold);"><?php echo format_price($booking['total_price']); ?></strong>
            </div>
        </div>
        
        <div style="display: flex; gap: 16px; justify-content: center;">
            <a href="index.php" class="btn btn-outline" style="flex: 1;">Back to Home</a>
            <a href="profile.php" class="btn" style="flex: 1;">View My Bookings</a>
        </div>
    </div>
</div>

<style>
@keyframes scaleIn {
    0% { transform: scale(0); }
    100% { transform: scale(1); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if(window.showToast) {
        setTimeout(() => window.showToast('Reservation confirmed successfully!'), 500);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
