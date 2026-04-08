<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_organizer();

$page_title = 'Sales Reports';
$organizer = get_organizer_by_user($_SESSION['user_id']);

// Get sales data - SQL injection vulnerability
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$sql = "SELECT e.title, e.event_date, COUNT(t.id) as tickets_sold, SUM(t.price) as revenue 
        FROM tickets t 
        JOIN events e ON t.event_id = e.id 
        WHERE e.organizer_id = {$organizer['id']} 
        AND t.payment_status = 'completed' 
        AND t.purchase_date BETWEEN '$start_date' AND '$end_date'
        GROUP BY e.id 
        ORDER BY revenue DESC";
$result = query($sql);
$sales_data = fetch_all($result);

$total_tickets = 0;
$total_revenue = 0;
foreach ($sales_data as $row) {
    $total_tickets += $row['tickets_sold'];
    $total_revenue += $row['revenue'];
}

include '../includes/header.php';
?>

<div class="reports">
    <h2>Sales Reports</h2>
    
    <form method="GET" class="date-filter">
        <label>From:</label>
        <input type="date" name="start_date" value="<?php echo $start_date; ?>">
        
        <label>To:</label>
        <input type="date" name="end_date" value="<?php echo $end_date; ?>">
        
        <button type="submit" class="btn">Filter</button>
    </form>
    
    <div class="report-summary">
        <div class="stat-card">
            <h3><?php echo $total_tickets; ?></h3>
            <p>Total Tickets Sold</p>
        </div>
        <div class="stat-card">
            <h3><?php echo format_currency($total_revenue); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <?php if (count($sales_data) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Event Date</th>
                    <th>Tickets Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_data as $row): ?>
                    <tr>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo format_date($row['event_date']); ?></td>
                        <td><?php echo $row['tickets_sold']; ?></td>
                        <td><?php echo format_currency($row['revenue']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="export-actions">
            <button onclick="exportToCSV()" class="btn btn-secondary">Export to CSV</button>
        </div>
    <?php else: ?>
        <p>No sales data for this period.</p>
    <?php endif; ?>
</div>

<script>
function exportToCSV() {
    alert('CSV export would happen here');
}
</script>

<?php include '../includes/footer.php'; ?>
