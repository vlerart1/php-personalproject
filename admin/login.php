<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('dashboard.php');
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area - ArtsHaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f4f7f6; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .admin-login-card { background: #fff; width: 100%; max-width: 400px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-control { background: #f9fafb; border: 1px solid #e5e7eb; color: #111; }
        .form-control:focus { border-color: var(--navy-dark); background: #fff; box-shadow: 0 0 0 3px rgba(11, 19, 43, 0.1); }
        .form-group label { color: #4b5563; font-weight: 500; }
        .btn-admin { background: var(--navy-dark); color: #fff; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; }
        .btn-admin:hover { background: var(--navy-light); }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 12px; border-radius: 6px; margin-bottom: 24px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="admin-login-card fade-in">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 64px; height: 64px; background: var(--navy-dark); color: var(--gold); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" style="height: 40px; width: auto;">
                    <path d="M 100 20 L 40 160 L 65 160 L 100 70 L 135 160 L 160 160 Z" fill="#ffffff" />
                    <path d="M 60 150 Q 100 60 140 150 Q 120 120 100 120 Q 80 120 60 150 Z" fill="#D4AF37" />
                </svg>
            </div>
            <h2 style="font-size: 1.5rem; color: #111;">ArtsHaven Admin</h2>
            <p style="color: #6b7280; font-size: 0.9rem; margin-top: 8px;">Enter your credentials to access the dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-admin">Sign In</button>
        </form>
    </div>
    <script>document.querySelector('.fade-in').classList.add('visible');</script>
</body>
</html>
