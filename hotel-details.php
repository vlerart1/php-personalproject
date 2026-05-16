<?php
require_once 'includes/header.php';

$hotel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$hotel_id) {
    redirect('hotels.php');
}

// Fetch hotel details
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ? AND status = 'active'");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    redirect('hotels.php');
}

// Fetch images
$img_stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY is_primary DESC");
$img_stmt->execute([$hotel_id]);
$images = $img_stmt->fetchAll();

// Mock images if none exist (for premium feel)
if (count($images) === 0) {
    $images = [
        ['image_path' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'],
        ['image_path' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
        ['image_path' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
        ['image_path' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80']
    ];
}

// Fetch amenities
$amenity_stmt = $pdo->prepare("
    SELECT a.* FROM amenities a 
    JOIN hotel_amenities ha ON a.id = ha.amenity_id 
    WHERE ha.hotel_id = ?
");
$amenity_stmt->execute([$hotel_id]);
$amenities = $amenity_stmt->fetchAll();

// Fetch room types
$room_stmt = $pdo->prepare("SELECT * FROM room_types WHERE hotel_id = ?");
$room_stmt->execute([$hotel_id]);
$rooms = $room_stmt->fetchAll();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!is_logged_in()) {
        redirect('auth/login.php');
    }
    
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, hotel_id, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $hotel_id, $rating, $comment]);
    
    $message = "Your review has been submitted and is awaiting moderation!";
}

// Fetch reviews (only approved ones)
$review_stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.hotel_id = ? AND r.status = 'approved'
    ORDER BY r.created_at DESC
");
$review_stmt->execute([$hotel_id]);
$reviews = $review_stmt->fetchAll();
?>

<?php if (isset($message)): ?>
    <script>document.addEventListener('DOMContentLoaded', () => window.showToast("<?php echo $message; ?>"));</script>
<?php endif; ?>

<div class="container hotel-details-page fade-in">
    <!-- Header Section -->
    <div class="header-section">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 8px;"><?php echo htmlspecialchars($hotel['name']); ?></h1>
                <p style="color: var(--gray-muted); font-size: 1.1rem; margin-bottom: 16px;">
                    <i class="fas fa-map-marker-alt" style="color: var(--gold);"></i> 
                    <?php echo htmlspecialchars($hotel['address'] . ', ' . $hotel['city'] . ', ' . $hotel['country']); ?>
                </p>
            </div>
            <div style="text-align: right;">
                <div style="display: inline-block; background: rgba(212, 175, 55, 0.1); padding: 8px 16px; border-radius: var(--radius-pill); color: var(--gold); font-size: 1.1rem; font-weight: 600;">
                    <i class="fas fa-star"></i> <?php echo $hotel['star_rating']; ?>.0
                </div>
                <div style="margin-top: 8px; color: var(--gray-muted); font-size: 0.9rem;">
                    <?php echo count($reviews); ?> reviews
                </div>
            </div>
        </div>
    </div>

    <!-- Image Gallery -->
    <div class="gallery">
        <div class="main-image">
            <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" alt="Main view">
        </div>
        <div class="side-images">
            <?php for($i=1; $i<=3; $i++): ?>
                <?php if(isset($images[$i])): ?>
                    <img src="<?php echo htmlspecialchars($images[$i]['image_path']); ?>" alt="Hotel view">
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <div class="main-content">
            
            <section class="details-section">
                <h2 style="font-size: 1.75rem; margin-bottom: 16px;">About this luxury stay</h2>
                <div style="color: var(--gray-light); line-height: 1.8; font-size: 1.05rem;">
                    <?php echo nl2br(htmlspecialchars($hotel['description'])); ?>
                </div>
            </section>
            
            <hr class="divider">

            <section class="details-section">
                <h2 style="font-size: 1.75rem; margin-bottom: 24px;">Premium Amenities</h2>
                <div class="amenities-grid">
                    <?php if (count($amenities) > 0): ?>
                        <?php foreach($amenities as $amenity): ?>
                            <div class="amenity-item">
                                <i class="fas fa-check" style="color: var(--gold);"></i>
                                <?php echo htmlspecialchars($amenity['name']); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="amenity-item"><i class="fas fa-wifi" style="color: var(--gold);"></i> High-speed WiFi</div>
                        <div class="amenity-item"><i class="fas fa-swimming-pool" style="color: var(--gold);"></i> Swimming Pool</div>
                        <div class="amenity-item"><i class="fas fa-spa" style="color: var(--gold);"></i> Spa & Wellness</div>
                        <div class="amenity-item"><i class="fas fa-dumbbell" style="color: var(--gold);"></i> Fitness Center</div>
                        <div class="amenity-item"><i class="fas fa-parking" style="color: var(--gold);"></i> Valet Parking</div>
                        <div class="amenity-item"><i class="fas fa-concierge-bell" style="color: var(--gold);"></i> 24/7 Concierge</div>
                    <?php endif; ?>
                </div>
            </section>
            
            <hr class="divider">

            <section class="details-section" id="rooms">
                <h2 style="font-size: 1.75rem; margin-bottom: 24px;">Select your room</h2>
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <?php if (count($rooms) > 0): ?>
                        <?php foreach($rooms as $room): ?>
                            <div class="room-card glass-card">
                                <div>
                                    <h3 style="font-size: 1.25rem; margin-bottom: 8px;"><?php echo htmlspecialchars($room['name']); ?></h3>
                                    <p style="color: var(--gray-muted); font-size: 0.9rem; margin-bottom: 16px;">
                                        <i class="fas fa-user-friends"></i> Up to <?php echo $room['capacity']; ?> guests
                                    </p>
                                    <p style="color: var(--gray-light); font-size: 0.95rem;"><?php echo htmlspecialchars($room['description']); ?></p>
                                </div>
                                <div style="text-align: right; min-width: 150px; display: flex; flex-direction: column; justify-content: center; align-items: flex-end; border-left: 1px solid var(--glass-border); padding-left: 24px;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--gold); margin-bottom: 4px;">
                                        <?php echo format_price($room['price_per_night']); ?>
                                    </div>
                                    <div style="color: var(--gray-muted); font-size: 0.85rem; margin-bottom: 16px;">per night</div>
                                    <button type="button" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;" onclick="selectRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars(addslashes($room['name'])); ?>')">Select</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No rooms currently available.</p>
                    <?php endif; ?>
                </div>
            </section>

            <hr class="divider">

            <section class="details-section">
                <h2 style="font-size: 1.75rem; margin-bottom: 24px;">Guest Reviews</h2>
                
                <?php if (is_logged_in()): ?>
                    <div class="glass-card" style="margin-bottom: 32px; padding: 24px;">
                        <h3 style="font-size: 1.25rem; margin-bottom: 16px;">Leave a Review</h3>
                        <form method="POST">
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label style="display: block; margin-bottom: 8px;">Rating</label>
                                <div class="rating-input" style="display: flex; gap: 8px; color: var(--gold); font-size: 1.5rem; cursor: pointer;">
                                    <input type="hidden" name="rating" id="ratingValue" value="5">
                                    <i class="fas fa-star" onclick="setRating(1)"></i>
                                    <i class="fas fa-star" onclick="setRating(2)"></i>
                                    <i class="fas fa-star" onclick="setRating(3)"></i>
                                    <i class="fas fa-star" onclick="setRating(4)"></i>
                                    <i class="fas fa-star" onclick="setRating(5)"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px;">Your Experience</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="Tell us about your stay..." required style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: #fff;"></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary" style="background: var(--gold); color: var(--navy-dark);">Post Review</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="glass-card" style="margin-bottom: 32px; padding: 24px; text-align: center;">
                        <p style="color: var(--gray-muted);">Please <a href="auth/login.php" style="color: var(--gold); font-weight: 600;">login</a> to leave a review.</p>
                    </div>
                <?php endif; ?>

                <?php if (count($reviews) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <?php foreach($reviews as $review): ?>
                            <div class="review-card glass-card" style="padding: 24px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--navy-light); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--gold);">
                                            <?php echo substr($review['first_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--gray-muted);"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <div style="color: var(--gold);">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p style="color: var(--gray-light); line-height: 1.6; font-style: italic;">"<?php echo nl2br(htmlspecialchars($review['comment'])); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--gray-muted);">No reviews yet for this property.</p>
                <?php endif; ?>
            </section>
        </div>

        <!-- Sticky Booking Sidebar -->
        <aside class="sidebar">
            <div class="booking-widget glass-card">
                <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--glass-border);">
                    <span style="font-size: 0.9rem; font-weight: 400; color: var(--gray-muted);">From</span> 
                    <?php echo format_price($hotel['min_price'] ?? 0); ?> 
                    <span style="font-size: 0.9rem; font-weight: 400; color: var(--gray-muted);">/ night</span>
                </div>
                
                <form action="booking.php" method="GET" id="bookingForm">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                    
                    <div class="form-group">
                        <label>Selected Room</label>
                        <select name="room_type_id" id="selectedRoomInput" class="form-control" required style="background: rgba(255,255,255,0.1);">
                            <option value="">Please select a room below</option>
                            <?php foreach($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>">
                                    <?php echo htmlspecialchars($room['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label>Check-in</label>
                            <input type="date" name="check_in" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label>Check-out</label>
                            <input type="date" name="check_out" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn w-100" style="padding: 16px;">Reserve Now</button>
                    <p style="text-align: center; font-size: 0.8rem; color: var(--gray-muted); margin-top: 16px;">You won't be charged yet</p>
                </form>
            </div>
        </aside>
    </div>
</div>

<script>
function selectRoom(id, name) {
    const select = document.getElementById('selectedRoomInput');
    select.value = id;
    window.scrollTo({ top: document.querySelector('.booking-widget').offsetTop - 100, behavior: 'smooth' });
    if(window.showToast) window.showToast('Selected: ' + name);
}

function setRating(val) {
    document.getElementById('ratingValue').value = val;
    const stars = document.querySelectorAll('.rating-input i');
    stars.forEach((star, index) => {
        if (index < val) {
            star.classList.remove('far');
            star.classList.add('fas');
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
        }
    });
}
</script>

<style>
.hotel-details-page { padding: 40px 24px; }
.header-section { margin-bottom: 32px; }

/* CSS Grid Gallery */
.gallery {
    display: grid;
    grid-template-columns: 2fr 1fr;
    grid-template-rows: 400px;
    gap: 16px;
    margin-bottom: 48px;
    border-radius: var(--radius-lg);
    overflow: hidden;
}
.main-image { height: 100%; }
.main-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth); cursor: pointer; }
.main-image img:hover { transform: scale(1.02); }
.side-images {
    display: grid;
    grid-template-rows: repeat(3, 1fr);
    gap: 16px;
    height: 100%;
}
.side-images img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth); cursor: pointer; }
.side-images img:hover { opacity: 0.8; }

/* Layout */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 48px;
}

.details-section { margin-bottom: 48px; }
.divider { border: 0; height: 1px; background: var(--glass-border); margin: 48px 0; }

/* Amenities Grid */
.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}
.amenity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.05rem;
    color: var(--gray-light);
}

/* Room Cards */
.room-card {
    display: flex;
    justify-content: space-between;
    padding: 24px;
}

/* Sidebar */
.sidebar { position: sticky; top: 100px; height: fit-content; }
.booking-widget { padding: 32px; }

@media (max-width: 992px) {
    .gallery { grid-template-columns: 1fr; grid-template-rows: auto; }
    .main-image { height: 300px; }
    .side-images { grid-template-columns: repeat(3, 1fr); grid-template-rows: 150px; }
    .content-grid { grid-template-columns: 1fr; }
    .room-card { flex-direction: column; gap: 24px; }
    .room-card > div:last-child { border-left: none; border-top: 1px solid var(--glass-border); padding-left: 0; padding-top: 24px; align-items: flex-start; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
