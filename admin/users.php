<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Admin - User Management';

// Get all users - vulnerable query
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = query($sql);
$users = fetch_all($result);

include '../includes/header.php';
?>

<div class="admin-panel">
    <h2>User Management</h2>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Role</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo format_datetime($user['created_at']); ?></td>
                    <td>
                        <a href="?edit=<?php echo $user['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
