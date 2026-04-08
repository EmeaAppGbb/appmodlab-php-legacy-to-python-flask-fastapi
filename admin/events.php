<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Admin - Event Management';

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = $_GET['delete'];
    delete_event($event_id);
    set_message('Event cancelled successfully', 'success');
    redirect($_SERVER['PHP_SELF']);
}

$events = get_all_events(500);

include '../includes/header.php';
?>

<div class="admin-panel">
    <h2>Event Management</h2>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Organizer</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo $event['id']; ?></td>
                    <td><?php echo $event['title']; ?></td>
                    <td><?php echo $event['organizer_name']; ?></td>
                    <td><?php echo format_date($event['event_date']); ?></td>
                    <td><?php echo $event['venue_name']; ?></td>
                    <td><?php echo $event['category_name']; ?></td>
                    <td><?php echo ucfirst($event['status']); ?></td>
                    <td>
                        <a href="../events/detail.php?id=<?php echo $event['id']; ?>">View</a> |
                        <a href="../events/edit.php?id=<?php echo $event['id']; ?>">Edit</a> |
                        <a href="?delete=<?php echo $event['id']; ?>" onclick="return confirm('Cancel this event?')">Cancel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
