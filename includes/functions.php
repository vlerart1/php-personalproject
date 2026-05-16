<?php
// includes/functions.php

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect to a specific URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Display formatted price
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Get star rating HTML
 */
function get_star_rating($rating) {
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star filled">&#9733;</span>';
        } else {
            $html .= '<span class="star">&#9734;</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>
