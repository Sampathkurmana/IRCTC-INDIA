<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit();
?>
