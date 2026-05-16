<?php
// seed.php
require_once 'config/database.php';

echo "Clearing existing data...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE hotel_amenities");
$pdo->exec("TRUNCATE TABLE amenities");
$pdo->exec("TRUNCATE TABLE reviews");
$pdo->exec("TRUNCATE TABLE booking_rooms");
$pdo->exec("TRUNCATE TABLE bookings");
$pdo->exec("TRUNCATE TABLE hotel_images");
$pdo->exec("TRUNCATE TABLE room_types");
$pdo->exec("TRUNCATE TABLE hotels");
$pdo->exec("TRUNCATE TABLE users");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "Inserting Random Users...\n";
$users_list = [
    ['first' => 'Emma', 'last' => 'Thompson', 'email' => 'emma@example.com'],
    ['first' => 'James', 'last' => 'Wilson', 'email' => 'james@example.com'],
    ['first' => 'Sofia', 'last' => 'Garcia', 'email' => 'sofia@example.com'],
    ['first' => 'Liam', 'last' => 'Smith', 'email' => 'liam@example.com'],
    ['first' => 'Olivia', 'last' => 'Chen', 'email' => 'olivia@example.com']
];
$user_ids = [];
$pass_hash = password_hash('password', PASSWORD_DEFAULT);
foreach ($users_list as $u) {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->execute([$u['first'], $u['last'], $u['email'], $pass_hash]);
    $user_ids[] = $pdo->lastInsertId();
}

echo "Inserting Amenities...\n";
$amenities = ['High-Speed WiFi', 'Infinity Pool', 'Luxury Spa', 'Fitness Center', 'Valet Parking', '24/7 Concierge', 'Rooftop Bar', 'Private Beach', 'Fine Dining Restaurant'];
$amenity_ids = [];
foreach ($amenities as $amenity) {
    $stmt = $pdo->prepare("INSERT INTO amenities (name) VALUES (?)");
    $stmt->execute([$amenity]);
    $amenity_ids[] = $pdo->lastInsertId();
}

$hotels = [
    [
        'name' => 'The Grand Parisian', 'city' => 'Paris', 'country' => 'France', 'rating' => 5,
        'desc' => 'Experience the romantic charm of Paris with unparalleled luxury. Located just steps from the Eiffel Tower, offering exquisite dining and lavish suites.',
        'image' => 'https://images.unsplash.com/photo-1517840901100-8179e982acb7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 850
    ],
    [
        'name' => 'Burj Al Horizon', 'city' => 'Dubai', 'country' => 'UAE', 'rating' => 5,
        'desc' => 'Soaring above the Arabian Gulf, this architectural marvel offers opulent suites, private helipads, and underwater dining.',
        'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 1200
    ],
    [
        'name' => 'The Thames Retreat', 'city' => 'London', 'country' => 'UK', 'rating' => 4,
        'desc' => 'A perfect blend of classic British elegance and modern luxury, situated right on the banks of the River Thames.',
        'image' => 'https://images.unsplash.com/photo-1529655683826-aba9b3e77383?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 450
    ],
    [
        'name' => 'Manhattan Skyline Suites', 'city' => 'New York', 'country' => 'USA', 'rating' => 5,
        'desc' => 'Unbeatable views of Central Park and the skyline. The ultimate urban sanctuary in the heart of NYC.',
        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 950
    ],
    [
        'name' => 'Tokyo Zen Garden Hotel', 'city' => 'Tokyo', 'country' => 'Japan', 'rating' => 5,
        'desc' => 'A peaceful retreat amidst the bustling city. Featuring traditional hot springs, minimalist design, and Michelin-starred sushi.',
        'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 600
    ],
    [
        'name' => 'Santorini Cliffside Villas', 'city' => 'Santorini', 'country' => 'Greece', 'rating' => 5,
        'desc' => 'Breathtaking sunset views from your private infinity pool overlooking the Aegean Sea.',
        'image' => 'https://images.unsplash.com/photo-1601918774946-25832a4be0d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 1100
    ],
    [
        'name' => 'Maldives Overwater Resort', 'city' => 'Malé', 'country' => 'Maldives', 'rating' => 5,
        'desc' => 'Wake up to the sound of crystal-clear waves in your private overwater bungalow.',
        'image' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 1500
    ],
    [
        'name' => 'Roman Colosseum Suites', 'city' => 'Rome', 'country' => 'Italy', 'rating' => 4,
        'desc' => 'Step into history with luxury suites located directly opposite the ancient Colosseum.',
        'image' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 380
    ],
    [
        'name' => 'Swiss Alpine Lodge', 'city' => 'Zermatt', 'country' => 'Switzerland', 'rating' => 5,
        'desc' => 'Premium ski-in/ski-out lodge with panoramic views of the Matterhorn and cozy fire-lit lounges.',
        'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 750
    ],
    [
        'name' => 'Sydney Harbor View Hotel', 'city' => 'Sydney', 'country' => 'Australia', 'rating' => 4,
        'desc' => 'Modern luxury right on the harbor with unobstructed views of the Opera House.',
        'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 420
    ],
    [
        'name' => 'Barcelona Gothic Palace', 'city' => 'Barcelona', 'country' => 'Spain', 'rating' => 5,
        'desc' => 'Stay in a restored 18th-century palace in the heart of the Gothic Quarter, featuring a rooftop pool and Michelin-starred tapas.',
        'image' => 'https://images.unsplash.com/photo-1583037189850-1921ae7c6c22?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 550
    ],
    [
        'name' => 'Bali Tropical Oasis', 'city' => 'Ubud', 'country' => 'Indonesia', 'rating' => 5,
        'desc' => 'Immerse yourself in the jungle with private pool villas, traditional Balinese spa treatments, and stunning valley views.',
        'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 680
    ],
    [
        'name' => 'Venice Canal Mansion', 'city' => 'Venice', 'country' => 'Italy', 'rating' => 5,
        'desc' => 'A historic mansion on the Grand Canal with private water taxi access, Murano glass chandeliers, and opulent Venetian decor.',
        'image' => 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 890
    ],
    [
        'name' => 'Cape Town Table View', 'city' => 'Cape Town', 'country' => 'South Africa', 'rating' => 5,
        'desc' => 'Modern waterfront luxury with panoramic views of Table Mountain and the Atlantic Ocean.',
        'image' => 'https://images.unsplash.com/photo-1580619305218-8423a7ef79b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 480
    ],
    [
        'name' => 'Singapore Marina Suites', 'city' => 'Singapore', 'country' => 'Singapore', 'rating' => 5,
        'desc' => 'World-famous architecture featuring the world\'s largest rooftop infinity pool and award-winning celebrity chef restaurants.',
        'image' => 'https://images.unsplash.com/photo-1529963183134-61a90db47eaf?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 1100
    ],
    [
        'name' => 'New York Central Park Tower', 'city' => 'New York', 'country' => 'USA', 'rating' => 5,
        'desc' => 'Sophisticated luxury soaring above Central Park, offering floor-to-ceiling windows and world-class service.',
        'image' => 'https://images.unsplash.com/photo-1541336032412-2048a678540d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'price' => 980
    ],
];

echo "Inserting Hotels, Rooms, Images, and Amenities...\n";
foreach ($hotels as $data) {
    // Insert Hotel
    $stmt = $pdo->prepare("INSERT INTO hotels (name, description, address, city, country, star_rating, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$data['name'], $data['desc'], '123 Luxury Ave', $data['city'], $data['country'], $data['rating']]);
    $hotel_id = $pdo->lastInsertId();

    // Insert Primary Image
    $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path, is_primary) VALUES (?, ?, 1)");
    $stmt->execute([$hotel_id, $data['image']]);

    // Insert 3 additional mock images
    $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path, is_primary) VALUES (?, ?, 0)");
    $stmt->execute([$hotel_id, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80']);
    $stmt->execute([$hotel_id, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80']);
    $stmt->execute([$hotel_id, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80']);

    // Insert Rooms
    $room_stmt = $pdo->prepare("INSERT INTO room_types (hotel_id, name, description, price_per_night, capacity, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $room_stmt->execute([$hotel_id, 'Deluxe King Room', 'Spacious room with a king-sized bed and city views.', $data['price'], 2, 10]);
    $room_stmt->execute([$hotel_id, 'Premium Suite', 'Luxury suite with a separate living area and premium amenities.', $data['price'] * 1.5, 4, 5]);
    $room_stmt->execute([$hotel_id, 'Presidential Penthouse', 'The ultimate luxury experience with panoramic views and private concierge.', $data['price'] * 3, 6, 1]);

    // Random Amenities
    shuffle($amenity_ids);
    for ($i = 0; $i < 5; $i++) {
        $pdo->prepare("INSERT INTO hotel_amenities (hotel_id, amenity_id) VALUES (?, ?)")->execute([$hotel_id, $amenity_ids[$i]]);
    }

    // Insert a few dummy reviews (Approved)
    $reviews_data = [
        ['rating' => 5, 'comment' => 'Absolutely stunning property! The service was impeccable and the views are unmatched.'],
        ['rating' => 4, 'comment' => 'Great experience overall. The room was clean and the staff was very helpful.']
    ];
    foreach ($reviews_data as $rev) {
        $random_user_id = $user_ids[array_rand($user_ids)];
        $pdo->prepare("INSERT INTO reviews (user_id, hotel_id, rating, comment, status) VALUES (?, ?, ?, ?, 'approved')")
            ->execute([$random_user_id, $hotel_id, $rev['rating'], $rev['comment']]);
    }
}

echo "Database seeded successfully!\n";
?>
