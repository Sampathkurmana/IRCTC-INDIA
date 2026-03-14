<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'railway_system');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
                <h2>Database Connection Failed</h2>
                <p>' . htmlspecialchars($conn->connect_error) . '</p>
                <p>Make sure XAMPP MySQL is running and you have imported <code>sql/railway.sql</code> in phpMyAdmin.</p>
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Generate a unique PNR number
function generatePNR() {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit();
}

// Flash message helper
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
