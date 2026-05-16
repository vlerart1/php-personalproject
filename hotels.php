<?php
require_once 'includes/header.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 8;
$offset = ($page - 1) * $per_page;

// Filtering setup
$destination = isset($_GET['destination']) ? sanitize($_GET['destination']) : '';
$star_rating = isset($_GET['star_rating']) ? (int)$_GET['star_rating'] : 0;
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popularity';

// Build query
$where_clauses = ["h.status = 'active'"];
$params = [];

if (!empty($destination)) {
    $where_clauses[] = "(h.city LIKE :dest OR h.country LIKE :dest OR h.name LIKE :dest)";
    $params[':dest'] = "%$destination%";
}

if ($star_rating > 0 && $star_rating <= 5) {
    $where_clauses[] = "h.star_rating = :stars";
    $params[':stars'] = $star_rating;
}

if ($guests > 0) {
    $where_clauses[] = "h.id IN (SELECT hotel_id FROM room_types WHERE capacity >= :guests)";
    $params[':guests'] = $guests;
}

$where_sql = implode(' AND ', $where_clauses);

// Sorting logic
$order_sql = "h.id DESC"; // Default popularity (newest)
if ($sort === 'price_asc') {
    $order_sql = "min_price ASC";
} elseif ($sort === 'price_desc') {
    $order_sql = "min_price DESC";
} elseif ($sort === 'rating_desc') {
    $order_sql = "h.star_rating DESC";
}

// Get total count for pagination
$count_query = "
    SELECT COUNT(DISTINCT h.id) as total 
    FROM hotels h 
    LEFT JOIN room_types r ON h.id = r.hotel_id 
    WHERE $where_sql
";
$count_stmt = $pdo->prepare($count_query);
foreach ($params as $key => $val) {
    $count_stmt->bindValue($key, $val);
}
$count_stmt->execute();
$total_hotels = $count_stmt->fetch()['total'];
$total_pages = ceil($total_hotels / $per_page);

// Get paginated results
$query = "
    SELECT h.id, h.name, h.city, h.country, h.star_rating, MIN(r.price_per_night) as min_price,
    (SELECT image_path FROM hotel_images hi WHERE hi.hotel_id = h.id AND hi.is_primary = 1 LIMIT 1) as primary_image
    FROM hotels h
    LEFT JOIN room_types r ON h.id = r.hotel_id
    WHERE $where_sql
    GROUP BY h.id
    ORDER BY $order_sql
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$hotels = $stmt->fetchAll();
?>

<!-- Header Banner -->
<div class="page-banner fade-in">
    <div class="container text-center">
        <h1 style="font-size: 3rem; margin-bottom: 16px;">Find Your Perfect Stay</h1>
        <p style="color: var(--gray-light); font-size: 1.1rem;">Browse through our curated collection of luxury accommodations.</p>
    </div>
</div>

<div class="container hotels-page fade-in">
    <!-- Sidebar -->
    <aside class="sidebar glass-card">
        <h3 style="margin-bottom: 24px; font-size: 1.25rem;"><i class="fas fa-filter" style="color: var(--gold);"></i> Filter Search</h3>
        <form action="hotels.php" method="GET">
            <div class="form-group">
                <label>Destination</label>
                <div style="position: relative;">
                    <i class="fas fa-map-marker-alt" style="position: absolute; left: 16px; top: 16px; color: var(--gray-muted);"></i>
                    <input type="text" name="destination" class="form-control" style="padding-left: 40px;" placeholder="City or hotel name" value="<?php echo htmlspecialchars($destination); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Star Rating</label>
                <select name="star_rating" class="form-control">
                    <option value="0">All Ratings</option>
                    <option value="5" <?php echo $star_rating == 5 ? 'selected' : ''; ?>>&#9733;&#9733;&#9733;&#9733;&#9733; 5 Stars</option>
                    <option value="4" <?php echo $star_rating == 4 ? 'selected' : ''; ?>>&#9733;&#9733;&#9733;&#9733;&#9734; 4 Stars & Up</option>
                    <option value="3" <?php echo $star_rating == 3 ? 'selected' : ''; ?>>&#9733;&#9733;&#9733;&#9734;&#9734; 3 Stars & Up</option>
                </select>
            </div>

            <div class="form-group">
                <label>Guests</label>
                <select name="guests" class="form-control">
                    <option value="0">Any number</option>
                    <option value="1" <?php echo $guests == 1 ? 'selected' : ''; ?>>1 Guest</option>
                    <option value="2" <?php echo $guests == 2 ? 'selected' : ''; ?>>2 Guests</option>
                    <option value="3" <?php echo $guests == 3 ? 'selected' : ''; ?>>3 Guests</option>
                    <option value="4" <?php echo $guests == 4 ? 'selected' : ''; ?>>4+ Guests</option>
                </select>
            </div>
            
            <button type="submit" class="btn w-100">Apply Filters</button>
            <?php if (!empty($destination) || $star_rating > 0 || $guests > 0): ?>
                <a href="hotels.php" style="display: block; text-align: center; margin-top: 16px; font-size: 0.9rem; color: var(--gray-muted);">Clear Filters</a>
            <?php endif; ?>
        </form>
    </aside>
    
    <!-- Results -->
    <main class="results-area">
        <div class="results-header glass-card">
            <p><strong><?php echo $total_hotels; ?></strong> luxury properties found</p>
            <div style="display: flex; align-items: center; gap: 16px;">
                <label style="font-size: 0.9rem; color: var(--gray-muted);">Sort by:</label>
                <form action="hotels.php" method="GET" id="sortForm">
                    <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                    <input type="hidden" name="star_rating" value="<?php echo $star_rating; ?>">
                    <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                    <select name="sort" class="form-control" style="width: auto; padding: 8px 16px;" onchange="document.getElementById('sortForm').submit();">
                        <option value="popularity" <?php echo $sort == 'popularity' ? 'selected' : ''; ?>>Popularity</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="rating_desc" <?php echo $sort == 'rating_desc' ? 'selected' : ''; ?>>Guest Rating</option>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="hotel-grid">
            <?php if (count($hotels) > 0): ?>
                <?php foreach ($hotels as $hotel): ?>
                    <div class="hotel-card glass-card" style="padding: 0; overflow: hidden;">
                        <div class="card-img-wrap" style="height: 220px; position: relative;">
                            <?php if ($hotel['primary_image']): ?>
                                <img src="<?php echo htmlspecialchars($hotel['primary_image']); ?>" alt="Hotel" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Placeholder" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php endif; ?>
                            <div style="position: absolute; top: 16px; right: 16px; background: rgba(11, 19, 43, 0.8); backdrop-filter: var(--glass-blur); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; color: var(--gold);">
                                <i class="fas fa-star"></i> <?php echo $hotel['star_rating']; ?>.0
                            </div>
                        </div>
                        
                        <div style="padding: 24px;">
                            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 8px;"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <p style="color: var(--gray-muted); margin-bottom: 20px; font-size: 0.9rem;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?></p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid var(--glass-border); padding-top: 16px;">
                                <div>
                                    <span style="font-size: 0.85rem; color: var(--gray-muted);">Starting from</span>
                                    <div style="font-size: 1.25rem; font-weight: 700; color: var(--gold);"><?php echo format_price($hotel['min_price'] ?? 0); ?></div>
                                </div>
                                <a href="hotel-details.php?id=<?php echo $hotel['id']; ?>" class="btn">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="glass-card text-center" style="grid-column: 1 / -1; padding: 64px;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--gray-muted); margin-bottom: 16px;"></i>
                    <h3>No hotels found</h3>
                    <p style="color: var(--gray-muted);">Try adjusting your filters or destination.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&destination=<?php echo urlencode($destination); ?>&star_rating=<?php echo $star_rating; ?>&guests=<?php echo $guests; ?>&sort=<?php echo $sort; ?>" 
                       class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
.page-banner {
    background: linear-gradient(135deg, rgba(11, 19, 43, 0.9), rgba(28, 37, 65, 0.9)), url('https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;
    padding: 100px 0 60px;
    margin-top: -80px;
    border-bottom: 1px solid var(--glass-border);
}

.hotels-page {
    display: flex;
    gap: 32px;
    padding: 40px 24px;
    margin-top: 0;
}

.sidebar {
    width: 320px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.results-area {
    flex: 1;
    margin-top: 0;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    margin-bottom: 32px;
}

.hotel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 48px;
}

.page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-sm);
    color: var(--white);
    font-weight: 500;
}

.page-link.active, .page-link:hover {
    background: var(--gold);
    border-color: var(--gold);
    color: var(--navy-dark);
}

@media (max-width: 992px) {
    .hotels-page {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
        position: static;
    }
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
