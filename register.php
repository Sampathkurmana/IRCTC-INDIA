<?php
$pageTitle = 'Register — IRCTC';
$rootPath  = '';
$cssPath   = '';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) redirect('index.php');

$errors = [];
$data   = ['name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($_POST['name'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone    = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $data = ['name'=>sanitize($_POST['name']??''), 'email'=>sanitize($_POST['email']??''), 'phone'=>$phone];

    if (strlen($data['name']) < 2)   $errors[] = 'Name must be at least 2 characters.';
    if (!$email)                      $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Phone must be a 10-digit number.';
    if (strlen($password) < 6)       $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)       $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name,email,password,phone) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $data['name'], $email, $hash, $phone);
            if ($stmt->execute()) {
                setFlash('success', 'Account created successfully! Please login.');
                redirect('login.php');
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
}
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
        <h2 class="auth-title">Create Account</h2>
        <p class="auth-sub">Join us to start booking train tickets across India.</p>

        <?php if ($errors): ?>
        <div class="flash flash-error">
            <span class="flash-icon">✕</span>
            <ul style="margin:0;padding-left:1.2rem;">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-icon-wrap">
                    <span class="icon">👤</span>
                    <input type="text" name="name" placeholder="Your full name" required
                           value="<?= htmlspecialchars($data['name']) ?>" maxlength="100">
                </div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-icon-wrap">
                    <span class="icon">✉</span>
                    <input type="email" name="email" placeholder="you@email.com" required
                           value="<?= htmlspecialchars($data['email']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <div class="input-icon-wrap">
                    <span class="icon">📱</span>
                    <input type="tel" name="phone" placeholder="10-digit mobile number" required
                           value="<?= htmlspecialchars($data['phone']) ?>" pattern="[0-9]{10}" maxlength="10">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.5rem;">
                Create Account →
            </button>
        </form>

        <div class="auth-divider"><span>Already have an account?</span></div>
        <a href="login.php" class="btn btn-outline btn-block">Login to IRCTC</a>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
