<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect(SITE_URL);
}

$page_title = 'Login';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // No CSRF protection!
    $username = $_POST['username']; // SQL injection vulnerability
    $password = $_POST['password'];
    
    if (login_user($username, $password)) {
        set_message('Welcome back, ' . $_SESSION['username'], 'success');
        redirect(SITE_URL);
    } else {
        set_message('Invalid username or password', 'error');
    }
}

include '../includes/header.php';
?>

<div class="form-container login-form">
    <h2>Login</h2>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p>Don't have an account? <a href="../register.php">Register here</a></p>
    
    <div class="demo-accounts">
        <h4>Demo Accounts:</h4>
        <p><strong>Admin:</strong> admin / admin123</p>
        <p><strong>Organizer:</strong> organizer1 / pass123</p>
        <p><strong>User:</strong> user1 / pass123</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
