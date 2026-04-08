<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_organizer();

$page_title = 'Organizer Settings';
$organizer = get_organizer_by_user($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = $_POST['company_name'];
    $description = $_POST['description'];
    $website = $_POST['website'];
    
    $sql = "UPDATE organizers SET 
            company_name = '$company_name', 
            description = '$description', 
            website = '$website' 
            WHERE id = {$organizer['id']}";
    query($sql);
    
    set_message('Settings updated successfully', 'success');
    redirect($_SERVER['PHP_SELF']);
}

include '../includes/header.php';
?>

<div class="settings">
    <h2>Organizer Settings</h2>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Company Name:</label>
            <input type="text" name="company_name" value="<?php echo $organizer['company_name']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" rows="5"><?php echo $organizer['description']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Website:</label>
            <input type="url" name="website" value="<?php echo $organizer['website']; ?>">
        </div>
        
        <div class="form-group">
            <label>Commission Rate:</label>
            <input type="text" value="<?php echo $organizer['commission_rate']; ?>%" disabled>
            <small>Contact admin to change commission rate</small>
        </div>
        
        <button type="submit" class="btn">Save Settings</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
