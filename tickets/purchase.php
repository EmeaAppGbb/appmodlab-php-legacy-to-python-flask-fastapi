<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$event_id = $_GET['event_id'];
$event = get_event($event_id);

if (!$event) {
    die('Event not found');
}

$page_title = 'Purchase Ticket';
$tickets_sold = get_tickets_sold($event_id);
$tickets_available = $event['max_capacity'] - $tickets_sold;

if ($tickets_available <= 0) {
    set_message('Sorry, this event is sold out', 'error');
    redirect(SITE_URL . '/events/detail.php?id=' . $event_id);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_type = $_POST['ticket_type'];
    $quantity = $_POST['quantity']; // No validation - could be negative!
    
    if ($quantity > $tickets_available) {
        set_message('Not enough tickets available', 'error');
    } else {
        // Create ticket records
        for ($i = 0; $i < $quantity; $i++) {
            $ticket_id = create_ticket($event_id, $_SESSION['user_id'], $ticket_type, $event['price']);
        }
        
        // Redirect to PayPal checkout
        redirect('checkout.php?ticket_id=' . $ticket_id);
    }
}

include '../includes/header.php';
?>

<div class="ticket-purchase">
    <h2>Purchase Tickets</h2>
    
    <div class="event-summary">
        <h3><?php echo $event['title']; ?></h3>
        <p><?php echo format_date($event['event_date']); ?> at <?php echo format_time($event['start_time']); ?></p>
        <p><?php echo $event['venue_name']; ?>, <?php echo $event['city']; ?></p>
        <p class="price">Price per ticket: <?php echo format_currency($event['price']); ?></p>
        <p>Tickets available: <?php echo $tickets_available; ?></p>
    </div>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Ticket Type:</label>
            <select name="ticket_type" required>
                <option value="general">General Admission</option>
                <option value="vip">VIP</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Quantity:</label>
            <input type="number" name="quantity" min="1" max="<?php echo $tickets_available; ?>" value="1" required>
        </div>
        
        <p class="total">Total: <span id="total"><?php echo format_currency($event['price']); ?></span></p>
        
        <button type="submit" class="btn btn-large">Continue to Payment</button>
    </form>
    
    <script>
        // Simple jQuery calculation
        $('input[name="quantity"]').on('input', function() {
            var quantity = $(this).val();
            var price = <?php echo $event['price']; ?>;
            var total = quantity * price;
            $('#total').text('$' + total.toFixed(2));
        });
    </script>
</div>

<?php include '../includes/footer.php'; ?>
