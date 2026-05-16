<?php
require_once 'includes/header.php';

if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('auth/login.php');
}

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$room_type_id = isset($_GET['room_type_id']) ? (int)$_GET['room_type_id'] : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

if (!$hotel_id || !$room_type_id || empty($check_in) || empty($check_out)) {
    redirect('hotels.php');
}

// Fetch hotel and room details
$stmt = $pdo->prepare("
    SELECT h.name as hotel_name, r.name as room_name, r.price_per_night,
    (SELECT image_path FROM hotel_images hi WHERE hi.hotel_id = h.id AND hi.is_primary = 1 LIMIT 1) as primary_image
    FROM hotels h 
    JOIN room_types r ON h.id = r.hotel_id 
    WHERE h.id = ? AND r.id = ?
");
$stmt->execute([$hotel_id, $room_type_id]);
$details = $stmt->fetch();

if (!$details) {
    redirect('hotels.php');
}

// Calculate days and total price
$datetime1 = new DateTime($check_in);
$datetime2 = new DateTime($check_out);
$interval = $datetime1->diff($datetime2);
$days = $interval->days;

if ($days <= 0) {
    $error = "Check-out date must be after check-in date.";
} else {
    $total_price = $days * $details['price_per_night'];
}

$img_url = $details['primary_image'] ? $details['primary_image'] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
?>

<div class="container fade-in" style="padding: 60px 24px; max-width: 1000px;">
    
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 8px;">Review your booking</h1>
        <p style="color: var(--gray-muted);">Please confirm the details of your stay before proceeding.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error" style="background: rgba(231, 76, 60, 0.2); padding: 16px; border-radius: 8px; color: #ff6b6b;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <a href="hotel-details.php?id=<?php echo $hotel_id; ?>" class="btn mt-4">Go Back to Selection</a>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px;" class="booking-grid">
            
            <div class="booking-form-area">
                <div class="glass-card" style="margin-bottom: 32px; padding: 32px;">
                    <h2 style="font-size: 1.5rem; margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 16px;">Guest Information</h2>
                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.9rem; color: var(--gray-muted); margin-bottom: 4px;">First Name</label>
                            <div style="font-weight: 500; font-size: 1.1rem;"><?php echo htmlspecialchars($_SESSION['first_name']); ?></div>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.9rem; color: var(--gray-muted); margin-bottom: 4px;">Last Name</label>
                            <div style="font-weight: 500; font-size: 1.1rem;"><?php echo htmlspecialchars($_SESSION['last_name']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="glass-card" style="padding: 32px;">
                    <h2 style="font-size: 1.5rem; margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 16px;">Special Requests</h2>
                    <form action="checkout.php" method="POST" id="checkoutForm">
                        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                        <input type="hidden" name="room_type_id" value="<?php echo $room_type_id; ?>">
                        <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                        <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                        
                        <div class="form-group">
                            <label style="color: var(--gray-light);">Have a special request? Let us know here (optional)</label>
                            <textarea name="special_requests" class="form-control" rows="4" placeholder="E.g., early check-in, late check-out, specific room location..."></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <div class="booking-summary-area">
                <div class="glass-card" style="padding: 0; overflow: hidden; position: sticky; top: 100px;">
                    <div style="height: 200px;">
                        <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Hotel" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    
                    <div style="padding: 24px;">
                        <h3 style="font-size: 1.25rem; margin-bottom: 4px;"><?php echo htmlspecialchars($details['hotel_name']); ?></h3>
                        <p style="color: var(--gray-muted); margin-bottom: 24px; font-size: 0.95rem;"><?php echo htmlspecialchars($details['room_name']); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--gray-muted);">Check-in</div>
                                <div style="font-weight: 500;"><?php echo date('M j, Y', strtotime($check_in)); ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--gray-muted);">Check-out</div>
                                <div style="font-weight: 500;"><?php echo date('M j, Y', strtotime($check_out)); ?></div>
                            </div>
                        </div>
                        
                        <div style="font-size: 0.9rem; color: var(--gray-muted); margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--glass-border);">
                            <i class="fas fa-moon" style="color: var(--gold);"></i> <?php echo $days; ?> night(s)
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                            <div style="font-size: 1.2rem; font-weight: 500;">Total</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--gold);"><?php echo format_price($total_price); ?></div>
                        </div>
                        
                        <button type="button" class="btn w-100" style="padding: 16px; font-size: 1.1rem;" onclick="document.getElementById('checkoutForm').submit();">Confirm & Pay</button>
                    </div>
                </div>
            </div>
            
        </div>
    <?php endif; ?>
</div>

<style>
@media (max-width: 768px) {
    .booking-grid { grid-template-columns: 1fr !important; }
    .booking-form-area { order: 2; }
    .booking-summary-area { order: 1; margin-bottom: 32px; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
