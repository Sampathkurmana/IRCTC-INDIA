<?php
$pageTitle = 'Search Trains — IRCTC';
$rootPath  = '';
$cssPath   = '';
require_once 'includes/header.php';

$source   = sanitize($_GET['source'] ?? '');
$dest     = sanitize($_GET['destination'] ?? '');
$date     = $_GET['journey_date'] ?? date('Y-m-d');
$trains   = [];
$searched = false;
$error    = '';

// Validate date
if ($date < date('Y-m-d')) $date = date('Y-m-d');

if ($source && $dest) {
    $searched = true;
    if (strtolower($source) === strtolower($dest)) {
        $error = 'Source and destination cannot be the same.';
    } else {
        $db   = getDB();
        $like_src  = '%' . $source . '%';
        $like_dest = '%' . $dest . '%';
        $stmt = $db->prepare("
            SELECT * FROM trains
            WHERE source LIKE ? AND destination LIKE ?
            ORDER BY departure_time ASC
        ");
        $stmt->bind_param('ss', $like_src, $like_dest);
        $stmt->execute();
        $trains = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

function seatClass($avail) {
    if ($avail == 0) return 'none';
    if ($avail <= 20) return 'low';
    return 'good';
}
function seatLabel($avail) {
    if ($avail == 0) return '⊘ No Seats';
    if ($avail <= 20) return '⚠ ' . $avail . ' left';
    return '✓ ' . $avail . ' available';
}
?>

<section style="background:var(--navy);padding:2rem 1.5rem;">
    <div class="container">
        <form action="search.php" method="GET" id="search-form">
            <div class="search-grid" style="background:rgba(255,255,255,0.07);border-radius:var(--radius);padding:1.25rem;">
                <div class="form-group">
                    <label style="color:rgba(255,255,255,0.6);">From</label>
                    <input type="text" id="source" name="source" placeholder="Source station" required
                           value="<?= htmlspecialchars($source) ?>" style="background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.2);color:white;">
                </div>
                <div class="form-group">
                    <label style="color:rgba(255,255,255,0.6);">To</label>
                    <input type="text" id="destination" name="destination" placeholder="Destination" required
                           value="<?= htmlspecialchars($dest) ?>" style="background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.2);color:white;">
                </div>
                <div class="form-group">
                    <label style="color:rgba(255,255,255,0.6);">Date</label>
                    <input type="date" id="journey_date" name="journey_date" required
                           value="<?= htmlspecialchars($date) ?>" min="<?= date('Y-m-d') ?>"
                           style="background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.2);color:white;">
                </div>
                <div>
                    <label style="color:transparent;font-size:0.78rem;">Search</label>
                    <button type="submit" class="btn btn-primary btn-block">🔍 Search</button>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="container" style="padding-top:2rem;padding-bottom:3rem;">

    <?php if ($error): ?>
    <div class="flash flash-error"><span class="flash-icon">✕</span><span><?= htmlspecialchars($error) ?></span></div>
    <?php endif; ?>

    <?php if ($searched && !$error): ?>

    <div class="flex-between mb-2">
        <div>
            <h2 class="section-title" style="margin-bottom:0;">
                <?= htmlspecialchars($source) ?> → <?= htmlspecialchars($dest) ?>
            </h2>
            <p class="text-muted" style="font-size:0.85rem;margin-top:4px;">
                <?= date('D, d M Y', strtotime($date)) ?> · <?= count($trains) ?> train(s) found
            </p>
        </div>
    </div>

    <?php if (empty($trains)): ?>
    <div class="empty-state">
        <div class="icon">🚉</div>
        <h3>No Trains Found</h3>
        <p>No trains found between <strong><?= htmlspecialchars($source) ?></strong> and <strong><?= htmlspecialchars($dest) ?></strong>.<br>
        Try checking the station names or search a different route.</p>
        <a href="search.php" class="btn btn-primary" style="margin-top:1rem;">Try Another Search</a>
    </div>

    <?php else: ?>
    <?php foreach ($trains as $t): ?>
    <div class="train-card">
        <div class="train-card-header">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="train-number-badge"><?= htmlspecialchars($t['train_number']) ?></span>
                <span class="train-name-text"><?= htmlspecialchars($t['train_name']) ?></span>
                <span class="train-type-tag"><?= htmlspecialchars($t['train_type']) ?></span>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php foreach (explode(',', $t['days_of_operation']) as $day): ?>
                <span class="day-tag"><?= trim($day) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="train-card-body">
            <div class="time-block">
                <div class="time"><?= substr($t['departure_time'],0,5) ?></div>
                <div class="station"><?= htmlspecialchars($t['source']) ?></div>
            </div>
            <div class="journey-line">
                <div class="dot"></div>
                <div class="line"></div>
                <div class="dot" style="background:var(--navy3);"></div>
            </div>
            <div class="time-block">
                <div class="time"><?= substr($t['arrival_time'],0,5) ?></div>
                <div class="station"><?= htmlspecialchars($t['destination']) ?></div>
            </div>
            <div class="seats-price">
                <div class="price-amount">₹<?= number_format($t['price'],2) ?> <span>/person</span></div>
                <div class="seats-avail <?= seatClass($t['available_seats']) ?>">
                    <?= seatLabel($t['available_seats']) ?>
                </div>
            </div>
        </div>
        <div class="train-card-footer">
            <div style="font-size:0.8rem;color:var(--muted);">
                🪑 <?= $t['total_seats'] ?> total seats
            </div>
            <?php if ($t['available_seats'] > 0): ?>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="book_ticket.php?train_id=<?= $t['id'] ?>&date=<?= urlencode($date) ?>"
               class="btn btn-primary btn-sm">Book Now →</a>
            <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Login to Book →</a>
            <?php endif; ?>
            <?php else: ?>
            <span class="badge badge-danger">Fully Booked</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state" style="padding:5rem 2rem;">
        <div class="icon">🔍</div>
        <h3>Search for Trains</h3>
        <p>Enter source, destination and journey date above to find available trains.</p>
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
