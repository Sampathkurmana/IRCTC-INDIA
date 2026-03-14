<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
if (isset($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please enter credentials.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: dashboard.php'); exit();
        } else {
            $error = 'Invalid credentials or not an admin account.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login — RailYatra</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="icon-box">🔐</div>
            <span>Admin <span style="color:var(--saffron);">Panel</span></span>
        </div>
        <h2 class="auth-title">Admin Login</h2>
        <p class="auth-sub">Manage trains, bookings, and users.</p>

        <?php if ($error): ?>
        <div class="flash flash-error"><span class="flash-icon">✕</span><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" placeholder="admin@railway.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.75rem;">Admin Login →</button>
        </form>
        <div style="text-align:center;margin-top:1.25rem;">
            <a href="../index.php" style="font-size:0.85rem;color:var(--muted);">← Back to main site</a>
        </div>
        <div style="background:var(--off-white);border-radius:8px;padding:0.75rem 1rem;margin-top:1rem;font-size:0.78rem;color:var(--muted);">
            <strong style="color:var(--navy);">Default credentials:</strong><br>
            Email: admin@railway.com<br>
            Password: password
        </div>
    </div>
</div>
</body>
</html>
