<?php
$pageTitle = 'Login — IRCTC';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                redirect('admin/dashboard.php');
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $flash = getFlash();
                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect('index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            
            <span style="font-size: 35px;">IRC<span style="color:var(--saffron);">TC</span></span>
        </div>
        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-sub">Login to manage your bookings and journeys.</p>

        <?php if ($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>">
            <span class="flash-icon"><?= $flash['type']==='success'?'✓':'ℹ' ?></span>
            <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="flash flash-error">
            <span class="flash-icon">✕</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-icon-wrap">
                    <span class="icon">✉</span>
                    <input type="email" name="email" placeholder="you@email.com" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-icon-wrap">
                    <span class="icon">🔒</span>
                    <input type="password" name="password" placeholder="Your password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.75rem;">
                Login →
            </button>
        </form>

        <div class="auth-divider"><span>Don't have an account?</span></div>
        <a href="register.php" class="btn btn-outline btn-block">Create Free Account</a>

        <p style="text-align:center;font-size:0.78rem;color:var(--muted);margin-top:1rem;">
            Admin? <a href="admin/login.php" class="auth-link">Admin Login →</a>
        </p>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
