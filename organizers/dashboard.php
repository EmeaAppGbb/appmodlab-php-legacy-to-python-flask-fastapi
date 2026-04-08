<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_organizer();

$page_title = 'Organizer Dashboard';
$organizer = get_organizer_by_user($_SESSION['user_id']);
$events = get_organizer_events($organizer['id']);
$total_revenue = get_organizer_revenue($organizer['id']);

include '../includes/header.php';
?>

<div class="dashboard">
    <h2>Organizer Dashboard</h2>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3><?php echo count($events); ?></h3>
            <p>Total Events</p>
        </div>
        <div class="stat-card">
            <h3><?php echo format_currency($total_revenue); ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $organizer['verified'] ? 'Yes' : 'Pending'; ?></h3>
            <p>Verified Status</p>
        </div>
    </div>
    
    <div class="dashboard-actions">
        <a href="../events/create.php" class="btn">Create New Event</a>
        <a href="reports.php" class="btn btn-secondary">View Reports</a>
        <a href="settings.php" class="btn btn-secondary">Settings</a>
    </div>
    
    <div class="organizer-events">
        <h3>My Events</h3>
        
        <?php if (count($events) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Capacity</th>
                        <th>Tickets Sold</th>
                        <th>Revenue</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <?php
                        $tickets_sold = get_tickets_sold($event['id']);
                        $event_revenue = $tickets_sold * $event['price'];
                        ?>
                        <tr>
                            <td><?php echo $event['title']; ?></td>
                            <td><?php echo format_date($event['event_date']); ?></td>
                            <td><?php echo $event['venue_name']; ?>, <?php echo $event['city']; ?></td>
                            <td><?php echo $event['max_capacity']; ?></td>
                            <td><?php echo $tickets_sold; ?></td>
                            <td><?php echo format_currency($event_revenue); ?></td>
                            <td><?php echo ucfirst($event['status']); ?></td>
                            <td>
                                <a href="../events/detail.php?id=<?php echo $event['id']; ?>">View</a> |
                                <a href="../events/edit.php?id=<?php echo $event['id']; ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You haven't created any events yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
