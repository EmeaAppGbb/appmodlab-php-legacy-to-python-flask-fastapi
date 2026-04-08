<?php
// 2000+ line utility functions file (anti-pattern)
// This represents years of organic growth with no refactoring

// ========== EVENT FUNCTIONS ==========

function get_all_events($limit = 100) {
    $sql = "SELECT e.*, v.name as venue_name, v.city, u.name as organizer_name, c.name as category_name 
            FROM events e 
            LEFT JOIN venues v ON e.venue_id = v.id 
            LEFT JOIN users u ON e.organizer_id = u.id 
            LEFT JOIN categories c ON e.category = c.id 
            ORDER BY e.event_date DESC LIMIT $limit";
    $result = query($sql);
    return fetch_all($result);
}

function get_event($event_id) {
    // SQL injection vulnerability - direct interpolation
    $sql = "SELECT e.*, v.name as venue_name, v.address, v.city, v.capacity as venue_capacity,
            u.name as organizer_name, u.email as organizer_email, c.name as category_name 
            FROM events e 
            LEFT JOIN venues v ON e.venue_id = v.id 
            LEFT JOIN users u ON e.organizer_id = u.id 
            LEFT JOIN categories c ON e.category = c.id 
            WHERE e.id = $event_id";
    $result = query($sql);
    return fetch_assoc($result);
}

function get_upcoming_events() {
    $sql = "SELECT e.*, v.name as venue_name, v.city 
            FROM events e 
            LEFT JOIN venues v ON e.venue_id = v.id 
            WHERE e.event_date >= CURDATE() AND e.status = 'active' 
            ORDER BY e.event_date ASC LIMIT 10";
    $result = query($sql);
    return fetch_all($result);
}

function search_events($search_term) {
    // SQL injection vulnerability - LIKE with unsanitized input
    $sql = "SELECT e.*, v.name as venue_name, v.city 
            FROM events e 
            LEFT JOIN venues v ON e.venue_id = v.id 
            WHERE e.title LIKE '%$search_term%' OR e.description LIKE '%$search_term%'
            ORDER BY e.event_date DESC";
    $result = query($sql);
    return fetch_all($result);
}

function get_events_by_category($category_id) {
    $sql = "SELECT e.*, v.name as venue_name, v.city 
            FROM events e 
            LEFT JOIN venues v ON e.venue_id = v.id 
            WHERE e.category = $category_id AND e.status = 'active'
            ORDER BY e.event_date DESC";
    $result = query($sql);
    return fetch_all($result);
}

function create_event($title, $description, $venue_id, $organizer_id, $event_date, $start_time, $end_time, $category, $max_capacity, $price, $image_path) {
    // SQL injection vulnerability
    $sql = "INSERT INTO events (title, description, venue_id, organizer_id, event_date, start_time, end_time, category, max_capacity, price, image_path, status, created_at) 
            VALUES ('$title', '$description', $venue_id, $organizer_id, '$event_date', '$start_time', '$end_time', $category, $max_capacity, $price, '$image_path', 'active', NOW())";
    query($sql);
    return get_last_insert_id();
}

function update_event($event_id, $title, $description, $venue_id, $event_date, $start_time, $end_time, $category, $max_capacity, $price, $image_path = null) {
    $image_sql = $image_path ? ", image_path = '$image_path'" : "";
    $sql = "UPDATE events SET 
            title = '$title', 
            description = '$description', 
            venue_id = $venue_id, 
            event_date = '$event_date', 
            start_time = '$start_time', 
            end_time = '$end_time', 
            category = $category, 
            max_capacity = $max_capacity, 
            price = $price 
            $image_sql 
            WHERE id = $event_id";
    query($sql);
}

function delete_event($event_id) {
    $sql = "UPDATE events SET status = 'cancelled' WHERE id = $event_id";
    query($sql);
}

function get_tickets_sold($event_id) {
    $sql = "SELECT COUNT(*) as count FROM tickets WHERE event_id = $event_id AND payment_status = 'completed'";
    $result = query($sql);
    $row = fetch_assoc($result);
    return $row['count'];
}

function is_event_sold_out($event_id) {
    $event = get_event($event_id);
    $tickets_sold = get_tickets_sold($event_id);
    return $tickets_sold >= $event['max_capacity'];
}

// ========== VENUE FUNCTIONS ==========

function get_all_venues() {
    $sql = "SELECT * FROM venues ORDER BY name";
    $result = query($sql);
    return fetch_all($result);
}

function get_venue($venue_id) {
    $sql = "SELECT * FROM venues WHERE id = $venue_id";
    $result = query($sql);
    return fetch_assoc($result);
}

function create_venue($name, $address, $city, $capacity, $amenities, $contact_email, $latitude, $longitude) {
    $sql = "INSERT INTO venues (name, address, city, capacity, amenities, contact_email, latitude, longitude) 
            VALUES ('$name', '$address', '$city', $capacity, '$amenities', '$contact_email', $latitude, $longitude)";
    query($sql);
    return get_last_insert_id();
}

// ========== TICKET FUNCTIONS ==========

function get_user_tickets($user_id) {
    $sql = "SELECT t.*, e.title as event_title, e.event_date, e.start_time, v.name as venue_name, v.address 
            FROM tickets t 
            JOIN events e ON t.event_id = e.id 
            LEFT JOIN venues v ON e.venue_id = v.id 
            WHERE t.user_id = $user_id 
            ORDER BY e.event_date DESC";
    $result = query($sql);
    return fetch_all($result);
}

function create_ticket($event_id, $user_id, $ticket_type, $price, $payment_status = 'pending') {
    $qr_code = generate_qr_code();
    $sql = "INSERT INTO tickets (event_id, user_id, ticket_type, price, purchase_date, payment_status, qr_code) 
            VALUES ($event_id, $user_id, '$ticket_type', $price, NOW(), '$payment_status', '$qr_code')";
    query($sql);
    return get_last_insert_id();
}

function update_ticket_payment($ticket_id, $payment_status, $paypal_txn_id) {
    $sql = "UPDATE tickets SET payment_status = '$payment_status', paypal_txn_id = '$paypal_txn_id' WHERE id = $ticket_id";
    query($sql);
}

function get_ticket($ticket_id) {
    $sql = "SELECT * FROM tickets WHERE id = $ticket_id";
    $result = query($sql);
    return fetch_assoc($result);
}

function generate_qr_code() {
    return 'QR-' . strtoupper(md5(uniqid(rand(), true)));
}

// ========== CATEGORY FUNCTIONS ==========

function get_all_categories() {
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = query($sql);
    return fetch_all($result);
}

function get_category($category_id) {
    $sql = "SELECT * FROM categories WHERE id = $category_id";
    $result = query($sql);
    return fetch_assoc($result);
}

// ========== ORGANIZER FUNCTIONS ==========

function get_organizer_by_user($user_id) {
    $sql = "SELECT * FROM organizers WHERE user_id = $user_id";
    $result = query($sql);
    return fetch_assoc($result);
}

function get_organizer_events($organizer_id) {
    $sql = "SELECT e.*, v.name as venue_name, v.city 
            FROM events e 
            LEFT JOIN venues v ON e.venue_id = v.id 
            WHERE e.organizer_id = $organizer_id 
            ORDER BY e.event_date DESC";
    $result = query($sql);
    return fetch_all($result);
}

function create_organizer($user_id, $company_name, $description, $website) {
    $sql = "INSERT INTO organizers (user_id, company_name, description, website, verified, commission_rate) 
            VALUES ($user_id, '$company_name', '$description', '$website', 0, 10.00)";
    query($sql);
    return get_last_insert_id();
}

function get_organizer_revenue($organizer_id) {
    $sql = "SELECT SUM(t.price) as total 
            FROM tickets t 
            JOIN events e ON t.event_id = e.id 
            WHERE e.organizer_id = $organizer_id AND t.payment_status = 'completed'";
    $result = query($sql);
    $row = fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

// ========== REVIEW FUNCTIONS ==========

function get_event_reviews($event_id) {
    $sql = "SELECT r.*, u.name as user_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.event_id = $event_id 
            ORDER BY r.created_at DESC";
    $result = query($sql);
    return fetch_all($result);
}

function create_review($event_id, $user_id, $rating, $comment) {
    $sql = "INSERT INTO reviews (event_id, user_id, rating, comment, created_at) 
            VALUES ($event_id, $user_id, $rating, '$comment', NOW())";
    query($sql);
    return get_last_insert_id();
}

function get_event_average_rating($event_id) {
    $sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE event_id = $event_id";
    $result = query($sql);
    $row = fetch_assoc($result);
    return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
}

// ========== FILE UPLOAD FUNCTIONS ==========

function upload_event_image($file) {
    // No file validation - major security vulnerability!
    $target_dir = UPLOAD_DIR;
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    
    return false;
}

// ========== UTILITY FUNCTIONS ==========

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

function format_time($time) {
    return date('g:i A', strtotime($time));
}

function format_datetime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

function truncate_text($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function set_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function sanitize_output($text) {
    // Incomplete sanitization
    return htmlspecialchars($text);
}

// ========== EMAIL FUNCTIONS ==========

function send_email($to, $subject, $message) {
    // Using deprecated mail() function with no validation
    $headers = "From: noreply@citypulse.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function send_ticket_confirmation($ticket_id) {
    $ticket = get_ticket($ticket_id);
    $event = get_event($ticket['event_id']);
    $user = get_user($ticket['user_id']);
    
    $subject = "Ticket Confirmation - " . $event['title'];
    $message = "<h2>Thank you for your purchase!</h2>";
    $message .= "<p>Event: " . $event['title'] . "</p>";
    $message .= "<p>Date: " . format_date($event['event_date']) . "</p>";
    $message .= "<p>QR Code: " . $ticket['qr_code'] . "</p>";
    
    send_email($user['email'], $subject, $message);
}

// ========== PAYPAL FUNCTIONS ==========

function verify_paypal_ipn() {
    // No signature verification - major security vulnerability!
    // Just trust whatever PayPal sends
    return true;
}

function process_paypal_payment($ticket_id, $txn_id) {
    update_ticket_payment($ticket_id, 'completed', $txn_id);
    send_ticket_confirmation($ticket_id);
}

// ========== PAGINATION FUNCTIONS ==========

function paginate($total_items, $per_page, $current_page) {
    $total_pages = ceil($total_items / $per_page);
    $offset = ($current_page - 1) * $per_page;
    
    return array(
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'per_page' => $per_page
    );
}

// ========== VALIDATION FUNCTIONS (minimal) ==========

function validate_email($email) {
    // Basic validation only
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_required($value) {
    return !empty($value);
}

// No CSRF token validation!
// No XSS protection!
// No input sanitization!
// This is intentionally vulnerable legacy code
?>
