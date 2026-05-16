<?php
// admin/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin_logged_in()) {
    redirect('login.php');
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - ArtsHaven Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-color: #f3f4f6;
            --sidebar-bg: #111827;
            --sidebar-hover: #1f2937;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --accent: #d4af37;
            --accent-hover: #e0be4d;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-color); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar-bg); color: #fff; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 24px; font-size: 1.5rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); letter-spacing: -0.5px; }
        .sidebar-brand span { color: var(--accent); }
        .nav-items { flex: 1; padding: 24px 16px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background: var(--sidebar-hover); color: #fff; }
        .nav-item i { width: 20px; font-size: 1.1rem; }
        
        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 32px; width: calc(100% - 260px); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .header h1 { font-size: 1.8rem; font-weight: 600; }
        .user-menu { display: flex; align-items: center; gap: 16px; background: #fff; padding: 8px 16px; border-radius: 50px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .user-avatar { width: 32px; height: 32px; background: var(--sidebar-bg); color: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        
        /* Common UI */
        .card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border-color); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 1.1rem; font-weight: 600; }
        .card-body { padding: 24px; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; transition: 0.2s; text-decoration: none; }
        .btn-primary { background: var(--sidebar-bg); color: #fff; border: 1px solid var(--accent); }
        .btn-primary:hover { background: var(--sidebar-hover); border-color: var(--accent-hover); }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        .btn-danger { background: #fee2e2; color: #ef4444; }
        .btn-danger:hover { background: #fecaca; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px 24px; text-align: left; font-size: 0.9rem; }
        th { background: #f9fafb; font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        tr { border-bottom: 1px solid var(--border-color); transition: background 0.2s; }
        tr:hover { background: #f9fafb; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-main); font-size: 0.9rem; }
        .form-control { width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); outline: none; font-size: 0.9rem; transition: 0.2s; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1); }
        
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .badge.active, .badge.confirmed, .badge.completed { background: #d1fae5; color: #059669; }
        .badge.inactive, .badge.pending { background: #fef3c7; color: #d97706; }
        .badge.cancelled, .badge.blocked { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand" style="display: flex; align-items: center; gap: 12px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" style="height: 32px; width: auto;">
                <path d="M 100 20 L 40 160 L 65 160 L 100 70 L 135 160 L 160 160 Z" fill="#ffffff" />
                <path d="M 60 150 Q 100 60 140 150 Q 120 120 100 120 Q 80 120 60 150 Z" fill="#D4AF37" />
            </svg>
            Arts<span>Haven</span>
        </div>
        <nav class="nav-items">
            <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="hotels.php" class="nav-item <?php echo $current_page == 'hotels.php' ? 'active' : ''; ?>"><i class="fas fa-hotel"></i> Hotels</a>
            <a href="rooms.php" class="nav-item <?php echo $current_page == 'rooms.php' ? 'active' : ''; ?>"><i class="fas fa-bed"></i> Rooms</a>
            <a href="bookings.php" class="nav-item <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Bookings</a>
            <a href="users.php" class="nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a>
            <a href="reviews.php" class="nav-item <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>"><i class="fas fa-star"></i> Reviews</a>
            <a href="settings.php" class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div style="padding: 24px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="logout.php" class="nav-item" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>
    
    <main class="main-content">
        <div class="header">
            <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
            <div class="user-menu">
                <span style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
            </div>
        </div>
