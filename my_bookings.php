<?php
$pageTitle = 'My Bookings — IRCTC';
$rootPath  = '';
$cssPath   = '';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) { setFlash('error','Login required.'); redirect('login.php'); }

$db      = getDB();
$user_id = $_SESSION['user_id'];
$highlight = sanitize($_GET['highlight'] ?? '');

$stmt = $db->prepare("
    SELECT b.*, t.train_name, t.train_number, t.source, t.destination,
        t.departure_time, t.arrival_time, t.train_type
    FROM bookings b
    JOIN trains t ON b.train_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Load passengers for each booking
foreach ($bookings as &$booking) {
    $ps = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
    $ps->bind_param('i', $booking['id']);
    $ps->execute();
    $booking['passengers'] = $ps->get_result()->fetch_all(MYSQLI_ASSOC);
    $ps->close();
}
unset($booking);
?>
<?php require_once 'includes/header.php'; ?>

<div class="container page-wrap">
    <div class="flex-between mb-3">
        <div>
            <h1 class="section-title">My Bookings</h1>
            <p class="text-muted">All your train bookings &amp; journey history</p>
        </div>
        <a href="search.php" class="btn btn-primary">+ Book New Ticket</a>
    </div>

    <?php if (empty($bookings)): ?>
    <div class="empty-state">
        <div class="icon">🎫</div>
        <h3>No Bookings Yet</h3>
        <p>You haven't booked any tickets. Start by searching for trains.</p>
        <a href="search.php" class="btn btn-primary" style="margin-top:1rem;">Search Trains</a>
    </div>
    <?php else: ?>

    <?php foreach ($bookings as $b): ?>
    <?php
    $isPast      = strtotime($b['journey_date']) < strtotime(date('Y-m-d'));
    $isCancelled = $b['booking_status'] === 'cancelled';
    $isHighlight = $b['pnr_number'] === $highlight;
    ?>
    <div class="card mb-2" style="<?= $isHighlight ? 'border:2px solid var(--success);' : '' ?>">
        <?php if ($isHighlight): ?>
        <div style="background:var(--success);color:white;padding:0.5rem 1.5rem;font-size:0.82rem;font-weight:600;">
            ✓ Booking Confirmed! PNR: <?= htmlspecialchars($b['pnr_number']) ?>
        </div>
        <?php endif; ?>

        <div class="train-card-header" style="border-radius:<?= $isHighlight?'0':'var(--radius)' ?> <?= $isHighlight?'0':'var(--radius)' ?> 0 0;opacity:<?= $isCancelled?'0.6':'1' ?>;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="train-number-badge"><?= htmlspecialchars($b['train_number']) ?></span>
                <span class="train-name-text"><?= htmlspecialchars($b['train_name']) ?></span>
                <span class="train-type-tag"><?= htmlspecialchars($b['train_type']) ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <?php if ($isCancelled): ?>
                <span class="badge badge-danger">Cancelled</span>
                <?php elseif ($isPast): ?>
                <span class="badge badge-info">Completed</span>
                <?php else: ?>
                <span class="badge badge-success">Confirmed</span>
                <?php endif; ?>
                <span style="color:rgba(255,255,255,0.7);font-size:0.82rem;">PNR: <?= htmlspecialchars($b['pnr_number']) ?></span>
            </div>
        </div>

        <div style="padding:1.25rem 1.5rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
                <div>
                    <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Journey</div>
                    <div style="font-weight:600;font-size:0.92rem;margin-top:3px;">
                        <?= htmlspecialchars($b['source']) ?> → <?= htmlspecialchars($b['destination']) ?>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Date</div>
                    <div style="font-weight:600;font-size:0.92rem;margin-top:3px;"><?= date('d M Y', strtotime($b['journey_date'])) ?></div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Seats</div>
                    <div style="font-weight:600;font-size:0.92rem;margin-top:3px;"><?= $b['seat_count'] ?> seat(s)</div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Amount</div>
                    <div style="font-weight:700;font-size:0.92rem;margin-top:3px;color:var(--navy);">₹<?= number_format($b['total_amount'],2) ?></div>
                </div>
            </div>

            <!-- Passengers -->
            <?php if (!empty($b['passengers'])): ?>
            <div style="background:var(--off-white);border-radius:var(--radius-sm);padding:0.85rem 1rem;margin-bottom:1rem;">
                <div style="font-size:0.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Passengers</div>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                    <?php foreach ($b['passengers'] as $p): ?>
                    <div style="background:white;border:1px solid var(--border);border-radius:6px;padding:6px 12px;font-size:0.82rem;">
                        <span style="font-weight:600;"><?= htmlspecialchars($p['name']) ?></span>
                        <span style="color:var(--muted);"> · <?= $p['age'] ?>yrs · <?= $p['gender'] ?></span>
                        <span class="badge badge-navy" style="margin-left:6px;font-size:0.68rem;"><?= htmlspecialchars($p['seat_number']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                <a href="pnr_status.php?pnr=<?= urlencode($b['pnr_number']) ?>" class="btn btn-outline btn-sm">
                    🔍 View Ticket
                </a>
                <?php if (!$isCancelled && !$isPast): ?>
                <a href="cancel_ticket.php?pnr=<?= urlencode($b['pnr_number']) ?>"
                   class="btn btn-danger btn-sm cancel-booking-btn"
                   data-pnr="<?= htmlspecialchars($b['pnr_number']) ?>">
                   ✕ Cancel
                </a>
                <?php endif; ?>
                <span style="font-size:0.78rem;color:var(--muted);margin-left:auto;">
                    Booked: <?= date('d M Y, h:i A', strtotime($b['created_at'])) ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
