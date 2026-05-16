<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtsHaven | Premium Hotel Booking</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <div class="container">
        <div class="navbar">
            <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" style="height: 40px; width: auto;">
                    <path d="M 100 20 L 40 160 L 65 160 L 100 70 L 135 160 L 160 160 Z" fill="#ffffff" />
                    <path d="M 60 150 Q 100 60 140 150 Q 120 120 100 120 Q 80 120 60 150 Z" fill="#D4AF37" />
                </svg>
                Arts<span>Haven</span>
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="hotels.php">Hotels</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                        <li><a href="auth/logout.php" class="btn btn-outline">Logout</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php" class="btn">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<main>
