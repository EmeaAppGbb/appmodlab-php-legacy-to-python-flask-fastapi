<?php
// Authentication Functions - Using MD5 (insecure!)

function is_logged_in() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['message'] = 'Please login to access this page';
        $_SESSION['message_type'] = 'error';
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] == 'admin';
}

function is_organizer() {
    return is_logged_in() && ($_SESSION['role'] == 'organizer' || $_SESSION['role'] == 'admin');
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        $_SESSION['message'] = 'Access denied';
        $_SESSION['message_type'] = 'error';
        header('Location: ' . SITE_URL);
        exit;
    }
}

function require_organizer() {
    require_login();
    if (!is_organizer()) {
        $_SESSION['message'] = 'Access denied';
        $_SESSION['message_type'] = 'error';
        header('Location: ' . SITE_URL);
        exit;
    }
}

function login_user($username, $password) {
    // MD5 hashing - extremely insecure!
    $password_hash = md5($password);
    
    // SQL injection vulnerability - no prepared statements
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password_hash'";
    $result = query($sql);
    
    if ($row = fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['name'];
        return true;
    }
    
    return false;
}

function register_user($username, $email, $password, $name, $phone) {
    // MD5 hashing - extremely insecure!
    $password_hash = md5($password);
    
    // SQL injection vulnerability - string interpolation
    $sql = "INSERT INTO users (username, email, password, name, phone, role, created_at) 
            VALUES ('$username', '$email', '$password_hash', '$name', '$phone', 'user', NOW())";
    
    query($sql);
    return get_last_insert_id();
}

function get_user($user_id) {
    // SQL injection vulnerability
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = query($sql);
    return fetch_assoc($result);
}
?>
