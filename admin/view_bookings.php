<?php
$pageTitle = 'All Bookings — Admin';
require_once 'admin_header.php';

$db = getDB();

// Handle admin cancel
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $bid  = (int)$_GET['cancel'];
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id=? AND booking_status='confirmed'");
    $stmt->bind_param('i', $bid);
    $stmt->execute();
    $bk = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($bk) {
        $db->begin_transaction();
        try {
            $u = $db->prepare("UPDATE bookings SET booking_status='cancelled', payment_status='refunded' WHERE id=?");
            $u->bind_param('i', $bid); $u->execute(); $u->close();
            $u2 = $db->prepare("UPDATE trains SET available_seats=available_seats+? WHERE id=?");
            $u2->bind_param('ii', $bk['seat_count'], $bk['train_id']); $u2->execute(); $u2->close();
            $db->commit();
            setFlash('success', 'Booking cancelled and seats returned.');
        } catch (Exception $e) {
            $db->rollback();
            setFlash('error', 'Cancellation failed.');
        }
    } else {
        setFlash('error', 'Booking not found or already cancelled.');
    }
    redirect('view_bookings.php');
}

$filter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$sql = "
    SELECT b.*, u.name AS user_name, u.email AS user_email,
           t.train_name, t.train_number, t.source, t.destination
    FROM bookings b
    JOIN users u ON b.user_id=u.id
    JOIN trains t ON b.train_id=t.id
    WHERE 1=1
";
$params = []; $types = '';

if ($filter) {
    $sql .= " AND b.booking_status=?"; $params[] = $filter; $types .= 's';
}
if ($search) {
    $like = '%' . $search . '%';
    $sql .= " AND (b.pnr_number LIKE ? OR u.name LIKE ? OR t.train_number LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
$sql .= " ORDER BY b.created_at DESC";
$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$flash2 = getFlash();
?>
<?php if ($flash2): ?>
<div class="flash flash-<?= $flash2['type'] ?>" style="margin-bottom:1rem;">
    <span class="flash-icon"><?= $flash2['type']==='success'?'✓':'✕' ?></span>
    <span><?= htmlspecialchars($flash2['message']) ?></span>
</div>
<?php endif; ?>

<div class="flex-between mb-3">
    <h1 style="font-family:var(--font-head);font-size:1.3rem;font-weight:700;">
        All Bookings <span style="color:var(--muted);font-size:0.85rem;font-weight:400;">(<?= count($bookings) ?>)</span>
    </h1>
</div>

<form method="GET" action="" style="display:flex;gap:0.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    <input type="text" name="q" placeholder="Search PNR, user, train number..."
           value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;max-width:340px;">
    <select name="status" style="min-width:140px;">
        <option value="">All Statuses</option>
        <option value="confirmed" <?= $filter==='confirmed'?'selected':'' ?>>Confirmed</option>
        <option value="cancelled" <?= $filter==='cancelled'?'selected':'' ?>>Cancelled</option>
        <option value="waiting"   <?= $filter==='waiting'  ?'selected':'' ?>>Waiting</option>
    </select>
    <button type="submit" class="btn btn-dark btn-sm">Filter</button>
    <?php if ($filter || $search): ?><a href="view_bookings.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>PNR</th>
                <th>Passenger</th>
                <th>Train</th>
                <th>Route</th>
                <th>Journey Date</th>
                <th>Seats</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Booked On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
            <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:2rem;">No bookings found.</td></tr>
            <?php else: ?>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><span style="font-weight:700;font-family:var(--font-head);font-size:0.8rem;letter-spacing:0.05em;"><?= htmlspecialchars($b['pnr_number']) ?></span></td>
                <td>
                    <span style="font-weight:600;"><?= htmlspecialchars($b['user_name']) ?></span>
                    <div class="sub"><?= htmlspecialchars($b['user_email']) ?></div>
                </td>
                <td>
                    <span><?= htmlspecialchars($b['train_number']) ?></span>
                    <div class="sub"><?= htmlspecialchars($b['train_name']) ?></div>
                </td>
                <td style="font-size:0.8rem;"><?= htmlspecialchars($b['source']) ?> → <?= htmlspecialchars($b['destination']) ?></td>
                <td style="white-space:nowrap;"><?= date('d M Y', strtotime($b['journey_date'])) ?></td>
                <td><?= $b['seat_count'] ?></td>
                <td style="font-weight:700;">₹<?= number_format($b['total_amount'],2) ?></td>
                <td>
                    <?php $bClass = ['confirmed'=>'badge-success','cancelled'=>'badge-danger','waiting'=>'badge-warning']; ?>
                    <span class="badge <?= $bClass[$b['booking_status']] ?? 'badge-info' ?>"><?= ucfirst($b['booking_status']) ?></span>
                </td>
                <td style="font-size:0.78rem;white-space:nowrap;"><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                <td>
                    <?php if ($b['booking_status'] === 'confirmed'): ?>
                    <a href="view_bookings.php?cancel=<?= $b['id'] ?>"
                       class="btn btn-danger btn-sm confirm-action"
                       data-confirm="Cancel booking <?= htmlspecialchars($b['pnr_number']) ?>? Seats will be returned.">
                       ✕ Cancel
                    </a>
                    <?php else: ?>
                    <span style="font-size:0.78rem;color:var(--muted);">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'admin_footer.php'; ?>
