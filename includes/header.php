<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a></h1>
            <nav>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/events/list.php">Events</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/tickets/my-tickets.php">My Tickets</a></li>
                        <?php if (is_organizer()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/organizers/dashboard.php">Organizer Dashboard</a></li>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/admin/events.php">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo SITE_URL; ?>/includes/logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/admin/login.php">Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>
