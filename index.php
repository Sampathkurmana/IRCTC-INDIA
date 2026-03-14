<?php
$pageTitle = 'IRCTC — Book Train Tickets Online';
$rootPath  = '';
$cssPath   = '';
require_once 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-flag-bar"></div>
    <div class="hero-content">
        <div class="hero-badge"> India's Railway Booking System</div>
        <h1 style="color:#FF9933;" >Book Your <span style="color:#FFFFFF;">Train Journey</span><span style="color:#138808;"> Fast &amp; Easy</span></h1>
        <p>Search trains, check availability, book tickets &amp; manage your journeys — all in one place.</p>

        <div class="search-box">
            <form action="search.php" method="GET" id="search-form">
                <div class="search-grid">
                    <div class="form-group">
                        <label>From</label>
                        <input type="text" id="source" name="source" placeholder="e.g. New Delhi" required
                            value="<?= htmlspecialchars($_GET['source'] ?? '') ?>" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="text" id="destination" name="destination" placeholder="e.g. Mumbai Central" required
                            value="<?= htmlspecialchars($_GET['destination'] ?? '') ?>" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Journey Date</label>
                        <input type="date" id="journey_date" name="journey_date" required
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($_GET['journey_date'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg" style="width:100%">🔍 Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;margin-bottom:3rem;">
            
           
           
        </div>

        <!-- Popular Routes -->
        <h2 class="section-title">Popular Routes</h2>
        <p class="section-sub">Frequently booked train routes across India</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
            <?php
            $routes = [
                ['New Delhi', 'Mumbai Central', '12951', 'Mumbai Rajdhani'],
                ['New Delhi', 'Howrah',         '12301', 'Howrah Rajdhani'],
                ['New Delhi', 'Bengaluru',       '12627', 'Karnataka Express'],
                ['New Delhi', 'Chennai',         '12435', 'Rajdhani Express'],
                ['Mumbai Central', 'Ahmedabad',  '12009', 'Shatabdi Express'],
                ['Howrah', 'New Delhi',          '12019', 'Howrah Shatabdi'],
            ];
            foreach ($routes as $r): ?>
            <a href="search.php?source=<?= urlencode($r[0]) ?>&destination=<?= urlencode($r[1]) ?>&journey_date=<?= date('Y-m-d', strtotime('+1 day')) ?>"
               class="card" style="padding:1rem 1.25rem;display:flex;align-items:center;gap:12px;transition:all 0.2s;">
                <div style="font-size:1.5rem;">🚄</div>
                <div>
                    <div style="font-weight:600;font-size:0.88rem;"><?= $r[0] ?> → <?= $r[1] ?></div>
                    <div style="font-size:0.78rem;color:var(--muted);margin-top:2px;"><?= $r[2] ?> · <?= $r[3] ?></div>
                </div>
                <span style="margin-left:auto;font-size:0.8rem;color:var(--saffron);font-weight:600;">→</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PNR quick check -->
<section style="background:var(--navy);padding:3rem 1.5rem;text-align:center;">
    <div class="container-sm">
        <h2 style="font-family:var(--font-head);color:white;font-size:1.5rem;font-weight:700;margin-bottom:0.5rem;">Check PNR Status</h2>
        <p style="color:rgba(255,255,255,0.6);margin-bottom:1.5rem;font-size:0.92rem;">Enter your PNR number to get instant booking status</p>
        <form action="pnr_status.php" method="GET" style="display:flex;gap:1rem;max-width:440px;margin:0 auto;">
            <input type="text" name="pnr" id="pnr-input" placeholder="Enter PNR Number"
                   maxlength="10" style="flex:1;padding:0.75rem 1rem;border-radius:8px;border:none;font-size:0.95rem;">
            <button type="submit" class="btn btn-primary">Check</button>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
