<?php
// auth/login.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            $redirect_url = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '../index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect_url);
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ArtsHaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(11, 19, 43, 0.95), rgba(28, 37, 65, 0.95)), url('https://images.unsplash.com/photo-1542314831-c6a4d14cece2?ixlib=rb-4.0.3') center/cover;
            padding: 40px 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            padding: 48px;
        }
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--white);
            font-weight: 500;
            margin-bottom: 16px;
            transition: var(--transition-fast);
        }
        .social-btn:hover { background: rgba(255,255,255,0.1); }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: var(--gray-muted);
            font-size: 0.9rem;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid var(--glass-border); }
        .divider:not(:empty)::before { margin-right: 16px; }
        .divider:not(:empty)::after { margin-left: 16px; }
        .alert { padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
        .alert-error { background: rgba(231, 76, 60, 0.2); border: 1px solid rgba(231, 76, 60, 0.5); color: #ff6b6b; }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card glass-card fade-in">
            <div class="text-center" style="margin-bottom: 32px;">
                <a href="../index.php" style="font-size: 2rem; font-weight: 700; color: var(--white); letter-spacing: -0.5px; display: inline-flex; align-items: center; justify-content: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" style="height: 48px; width: auto;">
                        <path d="M 100 20 L 40 160 L 65 160 L 100 70 L 135 160 L 160 160 Z" fill="#ffffff" />
                        <path d="M 60 150 Q 100 60 140 150 Q 120 120 100 120 Q 80 120 60 150 Z" fill="#D4AF37" />
                    </svg>
                    Arts<span style="color: var(--gold);">Haven</span>
                </a>
                <h2 style="margin-top: 16px; font-weight: 500;">Welcome back</h2>
                <p style="color: var(--gray-muted); margin-top: 8px;">Please enter your details to sign in.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <button type="button" class="social-btn"><i class="fab fa-google"></i> Sign in with Google</button>
            <div class="divider">or sign in with email</div>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="margin-bottom: 0;">Password</label>
                        <a href="#" style="font-size: 0.85rem; font-weight: 500;">Forgot password?</a>
                    </div>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn w-100" style="padding: 14px; margin-top: 8px;">Sign In</button>
            </form>
            
            <p class="text-center mt-4" style="color: var(--gray-light); font-size: 0.95rem;">
                Don't have an account? <a href="register.php" style="font-weight: 600;">Create one</a>
            </p>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>document.querySelector('.fade-in').classList.add('visible');</script>
</body>
</html>
