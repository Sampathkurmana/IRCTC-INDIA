<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    setFlash('error', 'Please login to continue.');
    redirect('../login.php');
}
?>
