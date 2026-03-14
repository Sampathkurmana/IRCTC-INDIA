<?php
$pageTitle = 'Manage Trains — Admin';
require_once 'admin_header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tid = (int)$_GET['delete'];
    $check = $db->prepare("SELECT COUNT(*) as c FROM bookings WHERE train_id=? AND booking_status='confirmed'");
    $check->bind_param('i', $tid);
    $check->execute();
    $active = $check->get_result()->fetch_assoc()['c'];
    $check->close();
    if ($active > 0) {
        setFlash('error', 'Cannot delete — this train has ' . $active . ' active confirmed booking(s).');
    } else {
        $del = $db->prepare("DELETE FROM trains WHERE id=?");
        $del->bind_param('i', $tid);
        $del->execute();
        $del->close();
        setFlash('success', 'Train deleted successfully.');
    }
    redirect('manage_trains.php');
}

$search = sanitize($_GET['q'] ?? '');
if ($search) {
    $like = '%' . $search . '%';
    $stmt = $db->prepare("SELECT * FROM trains WHERE train_name LIKE ? OR train_number LIKE ? OR source LIKE ? OR destination LIKE ? ORDER BY train_number");
    $stmt->bind_param('ssss', $like, $like, $like, $like);
} else {
    $stmt = $db->prepare("SELECT * FROM trains ORDER BY train_number ASC");
}
$stmt->execute();
$trains = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Re-fetch flash after potential redirect
$flash = getFlash();
?>
<?php if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>" style="margin-bottom:1rem;">
    <span class="flash-icon"><?= $flash['type']==='success'?'✓':'✕' ?></span>
    <span><?= htmlspecialchars($flash['message']) ?></span>
</div>
<?php endif; ?>

<div class="flex-between mb-3">
    <h1 style="font-family:var(--font-head);font-size:1.3rem;font-weight:700;">
        All Trains <span style="color:var(--muted);font-size:0.85rem;font-weight:400;">(<?= count($trains) ?>)</span>
    </h1>
    <a href="add_train.php" class="btn btn-primary">➕ Add Train</a>
</div>

<form method="GET" action="" style="margin-bottom:1.25rem;display:flex;gap:0.75rem;">
    <input type="text" name="q" placeholder="Search by name, number, source, destination..."
           value="<?= htmlspecialchars($search) ?>" style="flex:1;max-width:420px;">
    <button type="submit" class="btn btn-dark btn-sm">Search</button>
    <?php if ($search): ?><a href="manage_trains.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Train #</th>
                <th>Name</th>
                <th>Type</th>
                <th>Route</th>
                <th>Timing</th>
                <th>Seats</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trains)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2rem;">
                <?= $search ? 'No trains match your search.' : 'No trains added yet.' ?>
            </td></tr>
            <?php else: ?>
            <?php foreach ($trains as $t): ?>
            <tr>
                <td>
                    <span style="font-weight:700;font-family:var(--font-head);"><?= htmlspecialchars($t['train_number']) ?></span>
                </td>
                <td><?= htmlspecialchars($t['train_name']) ?></td>
                <td><span class="badge badge-info"><?= htmlspecialchars($t['train_type']) ?></span></td>
                <td style="font-size:0.82rem;">
                    <?= htmlspecialchars($t['source']) ?> → <?= htmlspecialchars($t['destination']) ?>
                </td>
                <td style="font-size:0.82rem;white-space:nowrap;">
                    <?= substr($t['departure_time'],0,5) ?> → <?= substr($t['arrival_time'],0,5) ?>
                </td>
                <td>
                    <span><?= $t['available_seats'] ?></span>
                    <div class="sub">of <?= $t['total_seats'] ?></div>
                </td>
                <td style="font-weight:700;">₹<?= number_format($t['price'],0) ?></td>
                <td style="white-space:nowrap;">
                    <a href="edit_train.php?id=<?= $t['id'] ?>" class="btn btn-outline btn-sm">✏ Edit</a>
                    <a href="manage_trains.php?delete=<?= $t['id'] ?>"
                       class="btn btn-danger btn-sm confirm-action"
                       data-confirm="Delete train <?= htmlspecialchars($t['train_number']) ?> - <?= htmlspecialchars($t['train_name']) ?>? This cannot be undone.">
                       🗑 Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'admin_footer.php'; ?>
