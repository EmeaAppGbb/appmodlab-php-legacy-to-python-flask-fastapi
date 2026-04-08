<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// SQL injection vulnerability - direct $_GET access
$event_id = $_GET['id'];
$event = get_event($event_id);

if (!$event) {
    die('Event not found');
}

$page_title = $event['title'];
$tickets_sold = get_tickets_sold($event_id);
$tickets_available = $event['max_capacity'] - $tickets_sold;
$reviews = get_event_reviews($event_id);
$avg_rating = get_event_average_rating($event_id);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    require_login();
    
    $rating = $_POST['rating']; // No validation
    $comment = $_POST['comment']; // SQL injection vulnerability
    
    create_review($event_id, $_SESSION['user_id'], $rating, $comment);
    set_message('Review submitted successfully', 'success');
    redirect($_SERVER['PHP_SELF'] . '?id=' . $event_id);
}

include '../includes/header.php';
?>

<div class="event-detail">
    <div class="event-header">
        <?php if ($event['image_path']): ?>
            <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . $event['image_path']; ?>" alt="<?php echo $event['title']; ?>" class="event-image">
        <?php endif; ?>
        
        <div class="event-main-info">
            <h2><?php echo $event['title']; ?></h2>
            <p class="category"><?php echo $event['category_name']; ?></p>
            
            <?php if ($avg_rating > 0): ?>
                <p class="rating">★ <?php echo $avg_rating; ?> (<?php echo count($reviews); ?> reviews)</p>
            <?php endif; ?>
            
            <div class="event-meta">
                <p><strong>Date:</strong> <?php echo format_date($event['event_date']); ?></p>
                <p><strong>Time:</strong> <?php echo format_time($event['start_time']); ?> - <?php echo format_time($event['end_time']); ?></p>
                <p><strong>Venue:</strong> <?php echo $event['venue_name']; ?></p>
                <p><strong>Address:</strong> <?php echo $event['address']; ?>, <?php echo $event['city']; ?></p>
                <p><strong>Organizer:</strong> <?php echo $event['organizer_name']; ?></p>
                <p><strong>Price:</strong> <?php echo format_currency($event['price']); ?></p>
                <p><strong>Tickets Available:</strong> <?php echo $tickets_available; ?> / <?php echo $event['max_capacity']; ?></p>
            </div>
            
            <?php if ($tickets_available > 0): ?>
                <a href="../tickets/purchase.php?event_id=<?php echo $event_id; ?>" class="btn btn-large">Buy Tickets</a>
            <?php else: ?>
                <span class="sold-out">SOLD OUT</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="event-description">
        <h3>About This Event</h3>
        <p><?php echo nl2br($event['description']); ?></p>
    </div>
    
    <div class="event-reviews">
        <h3>Reviews (<?php echo count($reviews); ?>)</h3>
        
        <?php if (is_logged_in()): ?>
            <form method="POST" class="review-form">
                <h4>Write a Review</h4>
                <div class="form-group">
                    <label>Rating:</label>
                    <select name="rating" required>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Comment:</label>
                    <textarea name="comment" rows="4" required></textarea>
                </div>
                <button type="submit" name="submit_review" class="btn">Submit Review</button>
            </form>
        <?php endif; ?>
        
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <strong><?php echo $review['user_name']; ?></strong>
                        <span class="rating">★ <?php echo $review['rating']; ?></span>
                        <span class="date"><?php echo format_datetime($review['created_at']); ?></span>
                    </div>
                    <p><?php echo nl2br($review['comment']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
