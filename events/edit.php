<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_organizer();

$event_id = $_GET['id'];
$event = get_event($event_id);

if (!$event) {
    die('Event not found');
}

$organizer = get_organizer_by_user($_SESSION['user_id']);

// Check ownership
if ($event['organizer_id'] != $organizer['id'] && !is_admin()) {
    die('Access denied');
}

$page_title = 'Edit Event';
$venues = get_all_venues();
$categories = get_all_categories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $venue_id = $_POST['venue_id'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $category = $_POST['category'];
    $max_capacity = $_POST['max_capacity'];
    $price = $_POST['price'];
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_path = upload_event_image($_FILES['image']);
    }
    
    update_event($event_id, $title, $description, $venue_id, $event_date, $start_time, $end_time, $category, $max_capacity, $price, $image_path);
    
    set_message('Event updated successfully', 'success');
    redirect(SITE_URL . '/events/detail.php?id=' . $event_id);
}

include '../includes/header.php';
?>

<div class="form-container">
    <h2>Edit Event</h2>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Event Title:</label>
            <input type="text" name="title" value="<?php echo $event['title']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" rows="5" required><?php echo $event['description']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Venue:</label>
            <select name="venue_id" required>
                <?php foreach ($venues as $venue): ?>
                    <option value="<?php echo $venue['id']; ?>" <?php echo ($event['venue_id'] == $venue['id']) ? 'selected' : ''; ?>>
                        <?php echo $venue['name']; ?> - <?php echo $venue['city']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Category:</label>
            <select name="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($event['category'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Event Date:</label>
            <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Start Time:</label>
            <input type="time" name="start_time" value="<?php echo $event['start_time']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>End Time:</label>
            <input type="time" name="end_time" value="<?php echo $event['end_time']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Max Capacity:</label>
            <input type="number" name="max_capacity" value="<?php echo $event['max_capacity']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Ticket Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo $event['price']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Event Image:</label>
            <?php if ($event['image_path']): ?>
                <p>Current: <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . $event['image_path']; ?>" width="100"></p>
            <?php endif; ?>
            <input type="file" name="image">
        </div>
        
        <button type="submit" class="btn">Update Event</button>
        <a href="detail.php?id=<?php echo $event_id; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
