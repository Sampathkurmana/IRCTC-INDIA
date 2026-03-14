<?php
$pageTitle = 'Dashboard — Admin';
require_once 'admin_header.php';

$db = getDB();

// Stats
$stats = [];
$stats['trains']   = $db->query("SELECT COUNT(*) as c FROM trains")->fetch_assoc()['c'];
$stats['users']    = $db->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$stats['bookings'] = $db->query("SELECT COUNT(*) as c FROM bookings WHERE booking_status='confirmed'")->fetch_assoc()['c'];
$stats['revenue']  = $db->query("SELECT COALESCE(SUM(total_amount),0) as r FROM bookings WHERE booking_status='confirmed'")->fetch_assoc()['r'];
$stats['cancelled']= $db->query("SELECT COUNT(*) as c FROM bookings WHERE booking_status='cancelled'")->fetch_assoc()['c'];

// Recent bookings
$recent = $db->query("
    SELECT b.*, u.name AS user_name, t.train_name, t.train_number, t.source, t.destination
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN trains t ON b.train_id = t.id
    ORDER BY b.created_at DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon orange">🚆</div>
        <div class="stat-info">
            <div class="num"><?= $stats['trains'] ?></div>
            <div class="label">Total Trains</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">👥</div>
        <div class="stat-info">
            <div class="num"><?= $stats['users'] ?></div>
            <div class="label">Registered Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🎫</div>
        <div class="stat-info">
            <div class="num"><?= $stats['bookings'] ?></div>
            <div class="label">Confirmed Bookings</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">💰</div>
        <div class="stat-info">
            <div class="num" style="font-size:1.3rem;">₹<?= number_format($stats['revenue'],0) ?></div>
            <div class="label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">✕</div>
        <div class="stat-info">
            <div class="num"><?= $stats['cancelled'] ?></div>
            <div class="label">Cancellations</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:center;margin-bottom:1rem;">
    <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;">Recent Bookings</h2>
    <a href="view_bookings.php" class="btn btn-outline btn-sm">View All →</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>PNR</th>
                <th>Passenger</th>
                <th>Train</th>
                <th>Route</th>
                <th>Date</th>
                <th>Seats</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2rem;">No bookings yet.</td></tr>
            <?php else: ?>
            <?php foreach ($recent as $b): ?>
            <tr>
                <td><span style="font-weight:700;font-family:var(--font-head);font-size:0.82rem;letter-spacing:0.05em;"><?= htmlspecialchars($b['pnr_number']) ?></span></td>
                <td><?= htmlspecialchars($b['user_name']) ?></td>
                <td>
                    <span><?= htmlspecialchars($b['train_number']) ?></span>
                    <div class="sub"><?= htmlspecialchars($b['train_name']) ?></div>
                </td>
                <td style="font-size:0.82rem;"><?= htmlspecialchars($b['source']) ?> → <?= htmlspecialchars($b['destination']) ?></td>
                <td style="white-space:nowrap;"><?= date('d M Y', strtotime($b['journey_date'])) ?></td>
                <td><?= $b['seat_count'] ?></td>
                <td style="font-weight:700;">₹<?= number_format($b['total_amount'],2) ?></td>
                <td>
                    <?php
                    $bClass = ['confirmed'=>'badge-success','cancelled'=>'badge-danger','waiting'=>'badge-warning'];
                    ?>
                    <span class="badge <?= $bClass[$b['booking_status']] ?? 'badge-info' ?>">
                        <?= ucfirst($b['booking_status']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'admin_footer.php'; ?>
