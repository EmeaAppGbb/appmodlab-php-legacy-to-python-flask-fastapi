<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = 'Home';

// Get upcoming events
$upcoming_events = get_upcoming_events();
$categories = get_all_categories();

include 'includes/header.php';
?>

<div class="hero">
    <h2>Discover Amazing Events in Your City</h2>
    <p>Find concerts, festivals, workshops, and more on CityPulse Events</p>
    
    <form action="events/search.php" method="GET" class="search-form">
        <input type="text" name="q" placeholder="Search events..." required>
        <button type="submit">Search</button>
    </form>
</div>

<section class="categories">
    <h3>Browse by Category</h3>
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a href="events/list.php?category=<?php echo $category['id']; ?>" class="category-card">
                <span class="icon"><?php echo $category['icon']; ?></span>
                <span class="name"><?php echo $category['name']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="upcoming-events">
    <h3>Upcoming Events</h3>
    <div class="event-grid">
        <?php foreach ($upcoming_events as $event): ?>
            <div class="event-card">
                <?php if ($event['image_path']): ?>
                    <img src="<?php echo UPLOAD_DIR . $event['image_path']; ?>" alt="<?php echo $event['title']; ?>">
                <?php else: ?>
                    <img src="css/placeholder.jpg" alt="<?php echo $event['title']; ?>">
                <?php endif; ?>
                <div class="event-info">
                    <h4><a href="events/detail.php?id=<?php echo $event['id']; ?>"><?php echo $event['title']; ?></a></h4>
                    <p class="date"><?php echo format_date($event['event_date']); ?></p>
                    <p class="venue"><?php echo $event['venue_name']; ?>, <?php echo $event['city']; ?></p>
                    <p class="price"><?php echo format_currency($event['price']); ?></p>
                    <a href="tickets/purchase.php?event_id=<?php echo $event['id']; ?>" class="btn">Buy Tickets</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
