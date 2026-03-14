<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="brand-name"> IRCTC</div>
            <p>India's trusted railway reservation system. Book tickets safely, check PNR status, and manage your journeys with ease.</p>
            <div class="footer-flag" style="margin-top:0.75rem;">
                <div class="flag-stripe" style="background:#FF9933;"></div>
                <div class="flag-stripe" style="background:#ffffff;"></div>
                <div class="flag-stripe" style="background:#138808;"></div>
            </div>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="<?= $rootPath ?? '../' ?>index.php">Home</a>
            <a href="<?= $rootPath ?? '../' ?>search.php">Search Trains</a>
            <a href="<?= $rootPath ?? '../' ?>pnr_status.php">PNR Status</a>
            <a href="<?= $rootPath ?? '../' ?>my_bookings.php">My Bookings</a>
        </div>
        <div class="footer-col">
            <h4>Account</h4>
            <a href="<?= $rootPath ?? '../' ?>register.php">Register</a>
            <a href="<?= $rootPath ?? '../' ?>login.php">Login</a>
            <a href="<?= $rootPath ?? '../' ?>admin/login.php">Admin Panel</a>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?= date('Y') ?> IRCTC — Indian Railway Catering and Tourism Corporation</span>
        <span style="color:rgba(255,255,255,0.35);">Built for educational purposes</span>
    </div>
</footer>
<script src="<?= $rootPath ?? '../' ?>js/script.js"></script>
</body>
</html>
