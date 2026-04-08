<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_organizer();

$page_title = 'Create Event';
$organizer = get_organizer_by_user($_SESSION['user_id']);
$venues = get_all_venues();
$categories = get_all_categories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // No CSRF protection!
    // No input validation!
    $title = $_POST['title'];
    $description = $_POST['description'];
    $venue_id = $_POST['venue_id'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $category = $_POST['category'];
    $max_capacity = $_POST['max_capacity'];
    $price = $_POST['price'];
    
    // File upload with no validation - major security vulnerability!
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_path = upload_event_image($_FILES['image']);
    }
    
    $event_id = create_event($title, $description, $venue_id, $organizer['id'], $event_date, $start_time, $end_time, $category, $max_capacity, $price, $image_path);
    
    if ($event_id) {
        set_message('Event created successfully', 'success');
        redirect(SITE_URL . '/events/detail.php?id=' . $event_id);
    } else {
        set_message('Failed to create event', 'error');
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h2>Create New Event</h2>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Event Title:</label>
            <input type="text" name="title" required>
        </div>
        
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" rows="5" required></textarea>
        </div>
        
        <div class="form-group">
            <label>Venue:</label>
            <select name="venue_id" required>
                <option value="">Select Venue</option>
                <?php foreach ($venues as $venue): ?>
                    <option value="<?php echo $venue['id']; ?>"><?php echo $venue['name']; ?> - <?php echo $venue['city']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Category:</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Event Date:</label>
            <input type="date" name="event_date" required>
        </div>
        
        <div class="form-group">
            <label>Start Time:</label>
            <input type="time" name="start_time" required>
        </div>
        
        <div class="form-group">
            <label>End Time:</label>
            <input type="time" name="end_time" required>
        </div>
        
        <div class="form-group">
            <label>Max Capacity:</label>
            <input type="number" name="max_capacity" required>
        </div>
        
        <div class="form-group">
            <label>Ticket Price:</label>
            <input type="number" step="0.01" name="price" required>
        </div>
        
        <div class="form-group">
            <label>Event Image:</label>
            <input type="file" name="image">
        </div>
        
        <button type="submit" class="btn">Create Event</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
