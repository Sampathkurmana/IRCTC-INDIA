<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }
$adminName   = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$flash       = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin — IRCTC' ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔐</text></svg>">
</head>
<body>
<div class="admin-layout">

<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="admin-logo">
        <span style=" font-size: 25px;">IRC<span  style="color:var(--saffron);">TC</span ><span style="
color:#FF9933;
font-size:20px;
font-weight:bold;
" >&nbsp; IN</span><span style="
color:#FFFFFF;
background:black;
font-size:20px;
font-weight:bold;">DI</span><SPAN style="
color:#138808;
font-size:20px;
font-weight:bold;">A</SPAN> </span>
    </div>
    <nav class="admin-nav">
        <div class="admin-nav-section">Main</div>
        <a href="dashboard.php" class="<?= $currentPage==='dashboard'?'active':'' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="admin-nav-section">Trains</div>
        <a href="manage_trains.php" class="<?= $currentPage==='manage_trains'?'active':'' ?>">
            <span class="nav-icon">🚆</span> All Trains
        </a>
        <a href="add_train.php" class="<?= $currentPage==='add_train'?'active':'' ?>">
            <span class="nav-icon">➕</span> Add Train
        </a>

        <div class="admin-nav-section">Bookings</div>
        <a href="view_bookings.php" class="<?= $currentPage==='view_bookings'?'active':'' ?>">
            <span class="nav-icon">🎫</span> All Bookings
        </a>

        <div class="admin-nav-section">Users</div>
        <a href="manage_users.php" class="<?= $currentPage==='manage_users'?'active':'' ?>">
            <span class="nav-icon">👥</span> All Users
        </a>

        <div class="admin-nav-section">Site</div>
        <a href="../index.php" target="_blank">
            <span class="nav-icon">🌐</span> View Site
        </a>
        <a href="logout.php">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-topbar">
        <div class="admin-page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="font-size:0.85rem;color:var(--muted);">👤 <?= $adminName ?></div>
            <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
        </div>
    </div>

    <?php if ($flash): ?>
    <div style="padding:0 2rem;margin-top:1rem;">
        <div class="flash flash-<?= $flash['type'] ?>">
            <span class="flash-icon"><?= $flash['type']==='success'?'✓':'ℹ' ?></span>
            <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-content">
