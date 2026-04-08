<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$ticket_id = $_GET['ticket_id'];
$ticket = get_ticket($ticket_id);

if (!$ticket || $ticket['user_id'] != $_SESSION['user_id']) {
    die('Invalid ticket');
}

$event = get_event($ticket['event_id']);
$page_title = 'Checkout';

include '../includes/header.php';
?>

<div class="checkout">
    <h2>Complete Your Purchase</h2>
    
    <div class="order-summary">
        <h3>Order Summary</h3>
        <p><strong>Event:</strong> <?php echo $event['title']; ?></p>
        <p><strong>Date:</strong> <?php echo format_date($event['event_date']); ?></p>
        <p><strong>Ticket Type:</strong> <?php echo ucfirst($ticket['ticket_type']); ?></p>
        <p><strong>Price:</strong> <?php echo format_currency($ticket['price']); ?></p>
    </div>
    
    <!-- PayPal Integration - No signature verification! -->
    <form action="<?php echo PAYPAL_URL; ?>" method="post">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL; ?>">
        <input type="hidden" name="item_name" value="Ticket for <?php echo $event['title']; ?>">
        <input type="hidden" name="item_number" value="<?php echo $ticket_id; ?>">
        <input type="hidden" name="amount" value="<?php echo $ticket['price']; ?>">
        <input type="hidden" name="currency_code" value="USD">
        <input type="hidden" name="return" value="<?php echo SITE_URL; ?>/tickets/confirm.php?ticket_id=<?php echo $ticket_id; ?>">
        <input type="hidden" name="cancel_return" value="<?php echo SITE_URL; ?>/tickets/my-tickets.php">
        <input type="hidden" name="notify_url" value="<?php echo SITE_URL; ?>/tickets/confirm.php">
        
        <button type="submit" class="btn btn-large">
            <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" alt="PayPal">
            Pay with PayPal
        </button>
    </form>
    
    <p class="payment-note">You will be redirected to PayPal to complete your payment securely.</p>
</div>

<?php include '../includes/footer.php'; ?>
