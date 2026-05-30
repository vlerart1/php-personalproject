<?php
require_once 'includes/header.php';

// Fetch featured hotels (limit to 6)
$query = "
    SELECT h.id, h.name, h.city, h.country, h.star_rating, MIN(r.price_per_night) as min_price,
    (SELECT image_path FROM hotel_images hi WHERE hi.hotel_id = h.id AND hi.is_primary = 1 LIMIT 1) as primary_image
    FROM hotels h
    LEFT JOIN room_types r ON h.id = r.hotel_id
    WHERE h.status = 'active'
    GROUP BY h.id
    ORDER BY h.star_rating DESC, h.id DESC
    LIMIT 6
";
$stmt = $pdo->query($query);
$featured_hotels = $stmt->fetchAll();

// Popular destinations for the grid
$destinations = [
    ['city' => 'Paris', 'country' => 'France', 'image' => 'https://images.unsplash.com/photo-1499856871958-5b9627545d1a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
    ['city' => 'Dubai', 'country' => 'UAE', 'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
    ['city' => 'London', 'country' => 'UK', 'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
    ['city' => 'Tokyo', 'country' => 'Japan', 'image' => 'https://images.unsplash.com/photo-1536098561742-ca998e48cbcc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
];
?>

<!-- Hero Section -->
<section class="hero"
    style="position: relative; height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-top: -80px;">
    <!-- Background Image -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;">
        <img src="https://images.unsplash.com/photo-1542314831-c6a4d14cece2?w=1920&q=80" alt=""
            style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <!-- Overlay Gradient -->
    <div
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(11, 19, 43, 0.7), rgba(11, 19, 43, 0.4), var(--navy-dark)); z-index: -1;">
    </div>

    <div class="container fade-in text-center" style="z-index: 1;">
        <h1 style="font-size: 4rem; font-weight: 700; margin-bottom: 24px; text-shadow: 0 4px 20px rgba(0,0,0,0.5);">
            <?php echo t('hero_title'); ?></h1>
        <p style="font-size: 1.2rem; color: var(--gray-light); margin-bottom: 48px; font-weight: 300;"><?php echo t('hero_subtitle'); ?></p>

        <div class="search-widget glass-card" style="max-width: 1000px; margin: 0 auto; padding: 16px;">
            <form action="hotels.php" method="GET"
                style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; position: relative;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 16px; top: 16px; color: var(--gray-muted);"></i>
                    <input type="text" name="destination" class="form-control" placeholder="<?php echo t('search_destination'); ?>"
                        style="padding-left: 48px; border: none; background: rgba(255,255,255,0.05);">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <input type="date" name="check_in" class="form-control"
                        style="border: none; background: rgba(255,255,255,0.05);" required>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <input type="date" name="check_out" class="form-control"
                        style="border: none; background: rgba(255,255,255,0.05);" required>
                </div>
                <div style="flex: 1; min-width: 120px; position: relative;">
                    <i class="fas fa-user"
                        style="position: absolute; left: 16px; top: 16px; color: var(--gray-muted);"></i>
                    <select name="guests" class="form-control"
                        style="padding-left: 48px; border: none; background: rgba(255,255,255,0.05); color: var(--white); appearance: none;">
                        <option value="1" style="color: black;">1 Guest</option>
                        <option value="2" style="color: black;" selected>2 Guests</option>
                        <option value="3" style="color: black;">3 Guests</option>
                        <option value="4" style="color: black;">4+ Guests</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="padding: 16px 32px; height: 50px;"><?php echo t('search_button'); ?></button>
            </form>
        </div>
    </div>
</section>

<!-- Popular Destinations -->
<section class="destinations fade-in" style="padding: 80px 0;">
    <div class="container">
        <h2 style="font-size: 2.5rem; text-align: center; margin-bottom: 48px;"><?php echo t('popular_destinations'); ?></h2>

        <div class="destinations-grid">
            <?php foreach ($destinations as $dest): ?>
                <a href="hotels.php?destination=<?php echo urlencode($dest['city']); ?>" class="destination-card">
                    <img src="<?php echo htmlspecialchars($dest['image']); ?>"
                        alt="<?php echo htmlspecialchars($dest['city']); ?>">
                    <div class="destination-overlay">
                        <h3><?php echo htmlspecialchars($dest['city']); ?></h3>
                        <p><?php echo htmlspecialchars($dest['country']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Hotels -->
<section class="featured-hotels fade-in" style="padding: 80px 0; background: rgba(255,255,255,0.02);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px;">
            <div>
                <h2 style="font-size: 2.5rem; margin-bottom: 8px;"><?php echo t('featured_hotels'); ?></h2>
                <p style="color: var(--gray-muted);"><?php echo t('featured_subtitle'); ?></p>
            </div>
            <a href="hotels.php" class="btn btn-outline"><?php echo t('view_all'); ?></a>
        </div>

        <div class="hotel-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 32px;">
            <?php foreach ($featured_hotels as $hotel): ?>
                <div class="hotel-card glass-card skeleton-container"
                    style="padding: 0; overflow: hidden; position: relative;">
                    <!-- Popular Badge -->
                    <?php if ($hotel['star_rating'] == 5): ?>
                        <div
                            style="position: absolute; top: 16px; left: 16px; background: var(--gold); color: var(--navy-dark); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; z-index: 2; text-transform: uppercase;">
                            <i class="fas fa-fire"></i> Popular
                        </div>
                    <?php endif; ?>

                    <div class="card-img-wrap" style="height: 240px; position: relative; overflow: hidden;">
                        <?php if ($hotel['primary_image']): ?>
                            <img src="<?php echo htmlspecialchars($hotel['primary_image']); ?>" alt="Hotel"
                                style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth);"
                                class="hotel-img">
                        <?php else: ?>
                            <div class="skeleton" style="width: 100%; height: 100%;"></div>
                        <?php endif; ?>
                        <div
                            style="position: absolute; top: 16px; right: 16px; background: rgba(11, 19, 43, 0.8); backdrop-filter: var(--glass-blur); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; color: var(--gold);">
                            <i class="fas fa-star"></i> <?php echo $hotel['star_rating']; ?>.0
                        </div>
                    </div>

                    <div style="padding: 24px;">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 8px;">
                            <?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <p style="color: var(--gray-muted); margin-bottom: 20px; font-size: 0.9rem;"><i
                                class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?></p>

                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid var(--glass-border); padding-top: 16px;">
                            <div>
                                <span style="font-size: 0.85rem; color: var(--gray-muted);"><?php echo t('starting_from'); ?></span>
                                <div style="font-size: 1.25rem; font-weight: 700; color: var(--gold);">
                                    <?php echo format_price($hotel['min_price'] ?? 0); ?></div>
                            </div>
                            <a href="hotel-details.php?id=<?php echo $hotel['id']; ?>" class="btn"><?php echo t('view_details'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features / Why Choose Us -->
<section class="features fade-in" style="padding: 100px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
            <div class="text-center glass-card" style="border: none; background: transparent; box-shadow: none;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 24px; background: rgba(212, 175, 55, 0.1); color: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <i class="fas fa-tags"></i>
                </div>
                <h3 style="margin-bottom: 16px;">Best Prices Guarantee</h3>
                <p style="color: var(--gray-muted);">We offer the most competitive rates for luxury accommodations
                    globally.</p>
            </div>
            <div class="text-center glass-card" style="border: none; background: transparent; box-shadow: none;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 24px; background: rgba(212, 175, 55, 0.1); color: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 style="margin-bottom: 16px;">Secure Booking</h3>
                <p style="color: var(--gray-muted);">Your payments and personal information are protected with
                    military-grade encryption.</p>
            </div>
            <div class="text-center glass-card" style="border: none; background: transparent; box-shadow: none;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 24px; background: rgba(212, 175, 55, 0.1); color: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 style="margin-bottom: 16px;">24/7 Support</h3>
                <p style="color: var(--gray-muted);">Our global concierge team is available around the clock to assist
                    you.</p>
            </div>
            <div class="text-center glass-card" style="border: none; background: transparent; box-shadow: none;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 24px; background: rgba(212, 175, 55, 0.1); color: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="margin-bottom: 16px;">Verified Hotels</h3>
                <p style="color: var(--gray-muted);">Every property is rigorously vetted to meet our uncompromising
                    luxury standards.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials fade-in" style="padding: 80px 0; background: rgba(255,255,255,0.02);">
    <div class="container">
        <h2 style="font-size: 2.5rem; text-align: center; margin-bottom: 48px;">What Our Guests Say</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
            <div class="glass-card">
                <div style="color: var(--gold); font-size: 1.2rem; margin-bottom: 16px;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 24px;">"The booking process was
                    seamless, and the hotel exceeded our expectations. Truly a 5-star experience from start to finish."
                </p>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div
                        style="width: 48px; height: 48px; background: var(--navy-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--gold);">
                        SJ</div>
                    <div>
                        <div style="font-weight: 600;">Sarah Jenkins</div>
                        <div style="font-size: 0.85rem; color: var(--gray-muted);">Stayed in Paris</div>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div style="color: var(--gold); font-size: 1.2rem; margin-bottom: 16px;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 24px;">"I travel frequently for
                    business, and ArtsHaven is the only platform I trust to find premium accommodations reliably."</p>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div
                        style="width: 48px; height: 48px; background: var(--navy-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--gold);">
                        MC</div>
                    <div>
                        <div style="font-weight: 600;">Michael Chen</div>
                        <div style="font-size: 0.85rem; color: var(--gray-muted);">Stayed in Tokyo</div>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div style="color: var(--gold); font-size: 1.2rem; margin-bottom: 16px;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 24px;">"The exclusive deals and
                    responsive customer support made our honeymoon unforgettable. Highly recommend!"</p>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div
                        style="width: 48px; height: 48px; background: var(--navy-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--gold);">
                        ER</div>
                    <div>
                        <div style="font-weight: 600;">Emma Roberts</div>
                        <div style="font-size: 0.85rem; color: var(--gray-muted);">Stayed in Maldives</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter fade-in" style="padding: 100px 0;">
    <div class="container text-center">
        <div class="glass-card"
            style="max-width: 800px; margin: 0 auto; padding: 64px 32px; background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(11, 19, 43, 0.8));">
            <h2 style="font-size: 2.2rem; margin-bottom: 16px;">Unlock Secret Deals</h2>
            <p style="color: var(--gray-light); margin-bottom: 32px; font-size: 1.1rem;">Subscribe to our newsletter and
                receive exclusive luxury hotel discounts straight to your inbox.</p>
            <form style="display: flex; gap: 16px; max-width: 500px; margin: 0 auto;"
                onsubmit="event.preventDefault(); window.showToast('Subscribed successfully!'); this.reset();">
                <input type="email" class="form-control" placeholder="Enter your email address" required
                    style="flex: 1;">
                <button type="submit" class="btn">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<style>
    /* Destinations Grid */
    .destinations-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: 300px;
        gap: 24px;
    }

    .destinations-grid .destination-card:first-child {
        grid-column: span 2;
        grid-row: span 2;
        height: 624px;
        /* 300*2 + 24 gap */
    }

    .destination-card {
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        display: block;
        box-shadow: var(--shadow-soft);
    }

    .destination-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .destination-card:hover img {
        transform: scale(1.05);
    }

    .destination-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 40px 24px 24px;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
        color: var(--white);
    }

    .destination-overlay h3 {
        font-size: 1.5rem;
        margin-bottom: 4px;
    }

    .hotel-card:hover .hotel-img {
        transform: scale(1.05);
    }

    @media (max-width: 992px) {
        .destinations-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .destinations-grid .destination-card:first-child {
            grid-column: span 2;
            grid-row: span 1;
            height: 300px;
        }

        .search-widget form {
            flex-direction: column;
        }

        .search-widget form>div,
        .search-widget form>button {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .destinations-grid {
            grid-template-columns: 1fr;
        }

        .destinations-grid .destination-card:first-child {
            grid-column: 1;
        }

        .hero h1 {
            font-size: 2.5rem;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>