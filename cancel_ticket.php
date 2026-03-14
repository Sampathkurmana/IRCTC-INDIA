<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) { setFlash('error','Login required.'); redirect('login.php'); }

$pnr     = strtoupper(sanitize($_GET['pnr'] ?? ''));
$user_id = $_SESSION['user_id'];

if (!$pnr) { redirect('my_bookings.php'); }

$db   = getDB();
$stmt = $db->prepare("
    SELECT b.*, t.train_name, t.train_number, t.available_seats
    FROM bookings b JOIN trains t ON b.train_id = t.id
    WHERE b.pnr_number = ? AND b.user_id = ?
");
$stmt->bind_param('si', $pnr, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) { setFlash('error','Booking not found.'); redirect('my_bookings.php'); }
if ($booking['booking_status'] === 'cancelled') { setFlash('info','This booking is already cancelled.'); redirect('my_bookings.php'); }
if (strtotime($booking['journey_date']) < strtotime(date('Y-m-d'))) {
    setFlash('error','Cannot cancel a past journey.'); redirect('my_bookings.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("UPDATE bookings SET booking_status='cancelled', payment_status='refunded' WHERE id = ?");
        $stmt->bind_param('i', $booking['id']);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("UPDATE trains SET available_seats = available_seats + ? WHERE id = ?");
        $stmt->bind_param('ii', $booking['seat_count'], $booking['train_id']);
        $stmt->execute();
        $stmt->close();

        $db->commit();
        setFlash('success', 'Booking PNR ' . $pnr . ' has been cancelled and ' . $booking['seat_count'] . ' seat(s) returned.');
        redirect('my_bookings.php');
    } catch (Exception $e) {
        $db->rollback();
        setFlash('error', 'Cancellation failed. Please try again.');
        redirect('my_bookings.php');
    }
}

$pageTitle = 'Cancel Ticket — IRCTC';
$rootPath  = '';
$cssPath   = '';
require_once 'includes/header.php';
?>

<div class="container page-wrap" style="max-width:600px;">
    <h1 class="section-title">Cancel Ticket</h1>
    <p class="text-muted mb-3">Please review before confirming cancellation.</p>

    <div class="card mb-3">
        <div class="card-header"><h3>Booking Details</h3></div>
        <div class="card-body">
            <div class="summary-item">
                <span class="label">PNR Number</span>
                <span class="value" style="font-family:var(--font-head);letter-spacing:0.07em;"><?= htmlspecialchars($pnr) ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Train</span>
                <span class="value"><?= htmlspecialchars($booking['train_number']) ?> – <?= htmlspecialchars($booking['train_name']) ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Journey Date</span>
                <span class="value"><?= date('D, d M Y', strtotime($booking['journey_date'])) ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Seats</span>
                <span class="value"><?= $booking['seat_count'] ?></span>
            </div>
            <div class="summary-total">
                <span>Amount Paid</span>
                <span>₹<?= number_format($booking['total_amount'],2) ?></span>
            </div>
        </div>
    </div>

    <div class="flash flash-warning">
        <span class="flash-icon">⚠</span>
        <span>This action is <strong>permanent</strong> and cannot be undone. The seats will be returned to available inventory.</span>
    </div>

    <form method="POST" action="" style="display:flex;gap:1rem;margin-top:1.5rem;flex-wrap:wrap;">
        <input type="hidden" name="confirm_cancel" value="1">
        <button type="submit" class="btn btn-danger btn-lg">✕ Confirm Cancellation</button>
        <a href="my_bookings.php" class="btn btn-outline btn-lg">← Keep Booking</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
