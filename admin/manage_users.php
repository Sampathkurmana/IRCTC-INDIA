<?php
$pageTitle = 'Manage Users — Admin';
require_once 'admin_header.php';

$db     = getDB();
$search = sanitize($_GET['q'] ?? '');

if ($search) {
    $like = '%' . $search . '%';
    $stmt = $db->prepare("
        SELECT u.*, COUNT(b.id) AS booking_count
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id AND b.booking_status='confirmed'
        WHERE u.role='user' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)
        GROUP BY u.id ORDER BY u.created_at DESC
    ");
    $stmt->bind_param('sss', $like, $like, $like);
} else {
    $stmt = $db->prepare("
        SELECT u.*, COUNT(b.id) AS booking_count
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id AND b.booking_status='confirmed'
        WHERE u.role='user'
        GROUP BY u.id ORDER BY u.created_at DESC
    ");
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="flex-between mb-3">
    <h1 style="font-family:var(--font-head);font-size:1.3rem;font-weight:700;">
        Registered Users <span style="color:var(--muted);font-size:0.85rem;font-weight:400;">(<?= count($users) ?>)</span>
    </h1>
</div>

<form method="GET" action="" style="display:flex;gap:0.75rem;margin-bottom:1.25rem;">
    <input type="text" name="q" placeholder="Search by name, email or phone..."
           value="<?= htmlspecialchars($search) ?>" style="flex:1;max-width:380px;">
    <button type="submit" class="btn btn-dark btn-sm">Search</button>
    <?php if ($search): ?><a href="manage_users.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Active Bookings</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">No users found.</td></tr>
            <?php else: ?>
            <?php foreach ($users as $i => $u): ?>
            <tr>
                <td style="color:var(--muted);"><?= $i+1 ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--navy);color:white;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(substr($u['name'],0,1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($u['name']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone']) ?></td>
                <td>
                    <?php if ($u['booking_count'] > 0): ?>
                    <span class="badge badge-success"><?= $u['booking_count'] ?> booking(s)</span>
                    <?php else: ?>
                    <span style="color:var(--muted);font-size:0.82rem;">No bookings</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:0.82rem;color:var(--muted);"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'admin_footer.php'; ?>
