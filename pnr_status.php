<?php
$pageTitle = 'PNR Status — IRCTC';
$rootPath  = '';
$cssPath   = '';
require_once 'includes/header.php';

$pnr     = strtoupper(sanitize($_GET['pnr'] ?? ''));
$booking = null;
$passengers = [];

if ($pnr) {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT b.*, t.train_name, t.train_number, t.source, t.destination,
               t.departure_time, t.arrival_time, t.train_type, u.name AS user_name
        FROM bookings b
        JOIN trains t ON b.train_id = t.id
        JOIN users u ON b.user_id = u.id
        WHERE b.pnr_number = ?
    ");
    $stmt->bind_param('s', $pnr);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($booking) {
        $ps = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
        $ps->bind_param('i', $booking['id']);
        $ps->execute();
        $passengers = $ps->get_result()->fetch_all(MYSQLI_ASSOC);
        $ps->close();
    }
}
?>

<div class="container" style="padding:3rem 1.5rem;max-width:800px;">
    <h1 class="section-title">PNR Status</h1>
    <p class="section-sub">Check your booking and journey details</p>

    <div class="card mb-3">
        <div class="card-body">
            <form action="" method="GET" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
                <div class="form-group" style="flex:1;min-width:200px;margin:0;">
                    <label>PNR Number</label>
                    <input type="text" name="pnr" id="pnr-input" placeholder="Enter 10-character PNR"
                           value="<?= htmlspecialchars($pnr) ?>"
                           maxlength="10" style="text-transform:uppercase;font-weight:700;letter-spacing:0.1em;">
                </div>
                <button type="submit" class="btn btn-primary">Check Status</button>
            </form>
        </div>
    </div>

    <?php if ($pnr && !$booking): ?>
    <div class="flash flash-error">
        <span class="flash-icon">✕</span>
        <span>No booking found for PNR: <strong><?= htmlspecialchars($pnr) ?></strong>. Please check the PNR number.</span>
    </div>
    <?php endif; ?>

    <?php if ($booking): ?>
    <div class="ticket-card" id="ticket">
        <div class="ticket-header">
            <div class="ticket-train-info">
                <div class="train-title">
                    🚆 <?= htmlspecialchars($booking['train_name']) ?>
                </div>
                <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);margin-top:4px;">
                    <?= htmlspecialchars($booking['train_number']) ?> · <?= htmlspecialchars($booking['train_type']) ?>
                </div>
                <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);margin-top:2px;">
                    Passenger: <?= htmlspecialchars($booking['user_name']) ?>
                </div>
            </div>
            <div class="ticket-pnr">
                <div class="pnr-label">PNR Number</div>
                <div class="pnr-code"><?= htmlspecialchars($booking['pnr_number']) ?></div>
                <div style="margin-top:6px;">
                    <?php
                    $status = $booking['booking_status'];
                    $class  = 'status-'.$status;
                    $labels = ['confirmed'=>'✓ Confirmed','cancelled'=>'✕ Cancelled','waiting'=>'⏳ Waiting'];
                    ?>
                    <span class="<?= $class ?>"><?= $labels[$status] ?? ucfirst($status) ?></span>
                </div>
            </div>
        </div>

        <div class="ticket-route">
            <div>
                <div class="ticket-time"><?= substr($booking['departure_time'],0,5) ?></div>
                <div class="ticket-station"><?= htmlspecialchars($booking['source']) ?></div>
            </div>
            <div class="journey-line" style="flex:1;margin:0 1.5rem;">
                <div class="dot"></div>
                <div class="line"></div>
                <div class="dot" style="background:var(--navy3);"></div>
            </div>
            <div>
                <div class="ticket-time"><?= substr($booking['arrival_time'],0,5) ?></div>
                <div class="ticket-station"><?= htmlspecialchars($booking['destination']) ?></div>
            </div>
            <div style="margin-left:2rem;text-align:right;">
                <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;">Journey Date</div>
                <div style="font-weight:700;font-size:0.95rem;margin-top:3px;"><?= date('D, d M Y', strtotime($booking['journey_date'])) ?></div>
            </div>
        </div>

        <div class="ticket-passengers">
            <div style="font-size:0.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:0.75rem;">
                Passengers (<?= count($passengers) ?>)
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Seat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($passengers as $i => $p): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                            <td><?= $p['age'] ?> yrs</td>
                            <td><?= htmlspecialchars($p['gender']) ?></td>
                            <td><span class="badge badge-navy"><?= htmlspecialchars($p['seat_number']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;flex-wrap:wrap;gap:0.75rem;">
                <div style="font-size:0.85rem;">
                    <span style="color:var(--muted);">Total Paid:</span>
                    <strong style="font-size:1.1rem;margin-left:6px;">₹<?= number_format($booking['total_amount'],2) ?></strong>
                </div>
                <div style="display:flex;gap:0.75rem;">
                    <button id="print-ticket" class="btn btn-outline btn-sm">🖨 Print Ticket</button>
                    <?php if ($booking['booking_status'] === 'confirmed' && strtotime($booking['journey_date']) >= strtotime(date('Y-m-d'))): ?>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $booking['user_id']): ?>
                    <a href="cancel_ticket.php?pnr=<?= urlencode($booking['pnr_number']) ?>"
                       class="btn btn-danger btn-sm cancel-booking-btn"
                       data-pnr="<?= htmlspecialchars($booking['pnr_number']) ?>">✕ Cancel</a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
