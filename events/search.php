<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// SQL injection vulnerability - no sanitization
$search_term = isset($_GET['q']) ? $_GET['q'] : '';

$page_title = 'Search Results';
$events = array();

if ($search_term) {
    $events = search_events($search_term);
    $page_title = 'Search Results for: ' . $search_term;
}

include '../includes/header.php';
?>

<div class="page-header">
    <h2><?php echo $page_title; ?></h2>
</div>

<div class="search-form">
    <form method="GET" action="">
        <input type="text" name="q" value="<?php echo $search_term; ?>" placeholder="Search events..." required>
        <button type="submit">Search</button>
    </form>
</div>

<?php if ($search_term && count($events) > 0): ?>
    <p class="search-count">Found <?php echo count($events); ?> events</p>
    
    <div class="event-grid">
        <?php foreach ($events as $event): ?>
            <div class="event-card">
                <?php if ($event['image_path']): ?>
                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . $event['image_path']; ?>" alt="<?php echo $event['title']; ?>">
                <?php endif; ?>
                <div class="event-info">
                    <h4><a href="detail.php?id=<?php echo $event['id']; ?>"><?php echo $event['title']; ?></a></h4>
                    <p class="date"><?php echo format_date($event['event_date']); ?></p>
                    <p class="venue"><?php echo $event['venue_name']; ?>, <?php echo $event['city']; ?></p>
                    <p class="price"><?php echo format_currency($event['price']); ?></p>
                    <a href="../tickets/purchase.php?event_id=<?php echo $event['id']; ?>" class="btn">Buy Tickets</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif ($search_term): ?>
    <p class="no-results">No events found matching your search.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
