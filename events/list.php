<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Events';

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1; // SQL injection vulnerability
$per_page = 12;

// Filter by category if provided
$category_id = isset($_GET['category']) ? $_GET['category'] : null;

if ($category_id) {
    $events = get_events_by_category($category_id);
    $category = get_category($category_id);
    $page_title = $category['name'] . ' Events';
} else {
    $events = get_all_events(1000);
}

$categories = get_all_categories();

include '../includes/header.php';
?>

<div class="page-header">
    <h2><?php echo $page_title; ?></h2>
</div>

<div class="filters">
    <form method="GET" action="">
        <label>Filter by Category:</label>
        <select name="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo $cat['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="event-grid">
    <?php foreach ($events as $event): ?>
        <div class="event-card">
            <?php if ($event['image_path']): ?>
                <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . $event['image_path']; ?>" alt="<?php echo $event['title']; ?>">
            <?php else: ?>
                <div class="no-image">No Image</div>
            <?php endif; ?>
            <div class="event-info">
                <h4><a href="detail.php?id=<?php echo $event['id']; ?>"><?php echo $event['title']; ?></a></h4>
                <p class="date"><?php echo format_date($event['event_date']); ?> at <?php echo format_time($event['start_time']); ?></p>
                <p class="venue"><?php echo $event['venue_name']; ?>, <?php echo $event['city']; ?></p>
                <p class="category"><?php echo $event['category_name']; ?></p>
                <p class="price"><?php echo format_currency($event['price']); ?></p>
                
                <?php if (is_event_sold_out($event['id'])): ?>
                    <span class="sold-out">SOLD OUT</span>
                <?php else: ?>
                    <a href="../tickets/purchase.php?event_id=<?php echo $event['id']; ?>" class="btn">Buy Tickets</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (count($events) == 0): ?>
    <p class="no-results">No events found in this category.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
