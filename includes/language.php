<?php
// includes/language.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default language to English
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sq'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirect to remove the lang parameter from URL
    $redirect = $_SERVER['PHP_SELF'];
    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        $params = explode('&', $_SERVER['QUERY_STRING']);
        $params = array_filter($params, function($param) {
            return strpos($param, 'lang=') !== 0;
        });
        if (!empty($params)) {
            $redirect .= '?' . implode('&', $params);
        }
    }
    header("Location: $redirect");
    exit();
}

// Load language file
$lang = $_SESSION['lang'];
$lang_file = __DIR__ . '/../languages/' . $lang . '.php';

if (file_exists($lang_file)) {
    $translations = require $lang_file;
} else {
    // Fallback to English if language file doesn't exist
    $translations = require __DIR__ . '/../languages/en.php';
}

// Helper function to get translation
function t($key) {
    global $translations;
    return $translations[$key] ?? $key;
}
?>
