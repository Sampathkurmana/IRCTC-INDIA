<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? '';
$flash      = getFlash();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Indian Railway Reservation System' ?></title>
    <link rel="stylesheet" href="<?= $cssPath ?? '../' ?>css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚆</text></svg>">
</head>
<body>

<nav class="navbar">
    <a href="<?= $rootPath ?? '../' ?>index.php" class="navbar-brand">
        <span style=" font-size: 25px;">IRC<span  style="color:var(--saffron);">TC</span ><span style="
color:#FF9933;
font-size:10px;
font-weight:bold;
" >&nbsp; I</span><span style="
color:#FFFFFF;
background:black;
font-size:10px;
font-weight:bold;">N</span><SPAN style="
color:#138808;
font-size:10px;
font-weight:bold;">D</SPAN> </span>
        
    </a>
    <ul class="navbar-nav" id="mainNav">
        <li><a href="<?= $rootPath ?? '../' ?>index.php" class="<?= $currentPage==='index'?'active':'' ?>">Home</a></li>
        <li><a href="<?= $rootPath ?? '../' ?>search.php" class="<?= $currentPage==='search'?'active':'' ?>">Search Trains</a></li>
        <li><a href="<?= $rootPath ?? '../' ?>pnr_status.php" class="<?= $currentPage==='pnr_status'?'active':'' ?>">PNR Status</a></li>
        <?php if ($isLoggedIn): ?>
            <li><a href="<?= $rootPath ?? '../' ?>my_bookings.php" class="<?= $currentPage==='my_bookings'?'active':'' ?>">My Bookings</a></li>
            <li><a href="<?= $rootPath ?? '../' ?>logout.php">👤 <?= htmlspecialchars($userName) ?></a></li>
        <?php else: ?>
            <li><a href="<?= $rootPath ?? '../' ?>login.php" class="<?= $currentPage==='login'?'active':'' ?>">Login</a></li>
            <li><a href="<?= $rootPath ?? '../' ?>register.php" class="btn-nav-cta">Register</a></li>
        <?php endif; ?>
    </ul>
    <button class="navbar-toggle" id="navToggle" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>

<?php if ($flash): ?>
<div style="max-width:1140px;margin:1rem auto;padding:0 1.5rem;">
    <div class="flash flash-<?= $flash['type'] ?>">
        <span class="flash-icon"><?= $flash['type']==='success'?'✓':($flash['type']==='error'?'✕':'ℹ') ?></span>
        <span><?= htmlspecialchars($flash['message']) ?></span>
    </div>
</div>
<?php endif; ?>
