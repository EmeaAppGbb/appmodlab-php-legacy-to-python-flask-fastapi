<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = 'Register';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // No CSRF protection!
    // No input validation!
    $username = $_POST['username']; // SQL injection vulnerability
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    // Check if username exists (vulnerable query)
    $check_sql = "SELECT id FROM users WHERE username = '$username'";
    $check_result = query($check_sql);
    
    if (fetch_assoc($check_result)) {
        set_message('Username already exists', 'error');
    } else {
        $user_id = register_user($username, $email, $password, $name, $phone);
        
        if ($user_id) {
            set_message('Registration successful! Please login.', 'success');
            redirect(SITE_URL . '/admin/login.php');
        } else {
            set_message('Registration failed', 'error');
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Create an Account</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="phone" required>
        </div>
        
        <button type="submit" class="btn">Register</button>
    </form>
    
    <p>Already have an account? <a href="admin/login.php">Login here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
