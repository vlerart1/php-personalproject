<?php
// auth/register.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$first_name, $last_name, $email, $phone, $hash])) {
                $success = "Registration successful. You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - ArtsHaven</title>
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
            max-width: 550px;
            padding: 48px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            color: var(--gray-muted);
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .close-btn:hover {
            color: var(--white);
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
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--glass-border);
        }
        .divider:not(:empty)::before { margin-right: 16px; }
        .divider:not(:empty)::after { margin-left: 16px; }
        .alert { padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
        .alert-error { background: rgba(231, 76, 60, 0.2); border: 1px solid rgba(231, 76, 60, 0.5); color: #ff6b6b; }
        .alert-success { background: rgba(46, 204, 113, 0.2); border: 1px solid rgba(46, 204, 113, 0.5); color: #51cf66; }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card glass-card fade-in">
            <a href="../index.php" class="close-btn" title="Back to Home"><i class="fas fa-times"></i></a>
            <div class="text-center" style="margin-bottom: 32px;">
                <a href="../index.php" style="font-size: 2rem; font-weight: 700; color: var(--white); letter-spacing: -0.5px; display: inline-flex; align-items: center; justify-content: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" style="height: 48px; width: auto;">
                        <path d="M 100 20 L 40 160 L 65 160 L 100 70 L 135 160 L 160 160 Z" fill="#ffffff" />
                        <path d="M 60 150 Q 100 60 140 150 Q 120 120 100 120 Q 80 120 60 150 Z" fill="#D4AF37" />
                    </svg>
                    Arts<span style="color: var(--gold);">Haven</span>
                </a>
                <h2 style="margin-top: 16px; font-weight: 500;">Create your account</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
                <a href="login.php" class="btn w-100 mt-4">Proceed to Login</a>
            <?php else: ?>
                <button type="button" class="social-btn"><i class="fab fa-google"></i> Continue with Google</button>
                <div class="divider">or register with email</div>
            
                <form action="register.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number (Optional)</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn w-100" style="padding: 14px; margin-top: 8px;">Create Account</button>
                </form>
                
                <p class="text-center mt-4" style="color: var(--gray-light); font-size: 0.95rem;">
                    Already have an account? <a href="login.php" style="font-weight: 600;">Sign in</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>document.querySelector('.fade-in').classList.add('visible');</script>
</body>
</html>
