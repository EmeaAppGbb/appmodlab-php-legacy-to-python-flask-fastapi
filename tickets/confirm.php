<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// PayPal IPN Handler - No signature verification (major security flaw!)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // This is an IPN callback from PayPal
    // In real code, we should verify the IPN with PayPal
    // But this is legacy code with no security!
    
    $ticket_id = $_POST['item_number'];
    $txn_id = $_POST['txn_id'];
    $payment_status = $_POST['payment_status'];
    
    if ($payment_status == 'Completed') {
        process_paypal_payment($ticket_id, $txn_id);
    }
    
    exit; // IPN handling done
}

// This is the return URL after payment
require_login();

$ticket_id = $_GET['ticket_id'];
$ticket = get_ticket($ticket_id);

if (!$ticket) {
    die('Invalid ticket');
}

// Simulate payment completion (since we don't have real PayPal)
update_ticket_payment($ticket_id, 'completed', 'TXN-' . uniqid());

$event = get_event($ticket['event_id']);
$page_title = 'Payment Confirmed';

include '../includes/header.php';
?>

<div class="confirmation">
    <h2>✓ Payment Confirmed!</h2>
    
    <div class="success-message">
        <p>Thank you for your purchase! Your ticket has been confirmed.</p>
        <p>A confirmation email has been sent to <?php echo $_SESSION['email']; ?></p>
    </div>
    
    <div class="ticket-details">
        <h3>Ticket Details</h3>
        <p><strong>Event:</strong> <?php echo $event['title']; ?></p>
        <p><strong>Date:</strong> <?php echo format_date($event['event_date']); ?> at <?php echo format_time($event['start_time']); ?></p>
        <p><strong>Venue:</strong> <?php echo $event['venue_name']; ?>, <?php echo $event['city']; ?></p>
        <p><strong>QR Code:</strong> <?php echo $ticket['qr_code']; ?></p>
        <p><strong>Ticket Type:</strong> <?php echo ucfirst($ticket['ticket_type']); ?></p>
    </div>
    
    <div class="actions">
        <a href="my-tickets.php" class="btn">View My Tickets</a>
        <a href="<?php echo SITE_URL; ?>/events/detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Back to Event</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
