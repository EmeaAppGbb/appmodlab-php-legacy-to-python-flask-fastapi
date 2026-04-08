<?php
require_once '../config.php';

// Clear session
session_destroy();

// Redirect to home
header('Location: ' . SITE_URL);
exit;
?>
