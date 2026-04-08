<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$page_title = 'My Tickets';
$tickets = get_user_tickets($_SESSION['user_id']);

include '../includes/header.php';
?>

<div class="my-tickets">
    <h2>My Tickets</h2>
    
    <?php if (count($tickets) > 0): ?>
        <div class="tickets-list">
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card">
                    <div class="ticket-header">
                        <h3><?php echo $ticket['event_title']; ?></h3>
                        <span class="status <?php echo $ticket['payment_status']; ?>"><?php echo ucfirst($ticket['payment_status']); ?></span>
                    </div>
                    
                    <div class="ticket-details">
                        <p><strong>Date:</strong> <?php echo format_date($ticket['event_date']); ?> at <?php echo format_time($ticket['start_time']); ?></p>
                        <p><strong>Venue:</strong> <?php echo $ticket['venue_name']; ?></p>
                        <p><strong>Address:</strong> <?php echo $ticket['address']; ?></p>
                        <p><strong>Ticket Type:</strong> <?php echo ucfirst($ticket['ticket_type']); ?></p>
                        <p><strong>QR Code:</strong> <?php echo $ticket['qr_code']; ?></p>
                        <p><strong>Purchase Date:</strong> <?php echo format_datetime($ticket['purchase_date']); ?></p>
                        <p><strong>Price:</strong> <?php echo format_currency($ticket['price']); ?></p>
                    </div>
                    
                    <div class="ticket-actions">
                        <a href="../events/detail.php?id=<?php echo $ticket['event_id']; ?>" class="btn btn-small">View Event</a>
                        <button onclick="printTicket(<?php echo $ticket['id']; ?>)" class="btn btn-small btn-secondary">Print Ticket</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-tickets">You haven't purchased any tickets yet.</p>
        <a href="<?php echo SITE_URL; ?>/events/list.php" class="btn">Browse Events</a>
    <?php endif; ?>
</div>

<script>
function printTicket(ticketId) {
    alert('Printing ticket #' + ticketId);
    // In a real app, this would generate a printable ticket
}
</script>

<?php include '../includes/footer.php'; ?>
