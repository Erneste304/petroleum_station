<?php
require_once 'config/database.php';
include 'includes/header.php';

// Get database statistics with error handling
try {
    // Total stations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM station");
    $stats['stations'] = $stmt->fetch()['count'];
    
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employee");
    $stats['employees'] = $stmt->fetch()['count'];
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer");
    $stats['customers'] = $stmt->fetch()['count'];
    
    // Total fuel types
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM fuel_type");
    $stats['fuel_types'] = $stmt->fetch()['count'];
    
    // Today's sales
    $stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sale WHERE DATE(sale_date) = CURDATE()");
    $todaySales = $stmt->fetch();
    
    // Recent sales with joins
    $stmt = $pdo->query("
        SELECT s.*, c.name as customer_name, 
               CONCAT(e.first_name, ' ', e.last_name) as employee_name,
               f.fuel_name
        FROM sale s 
        LEFT JOIN customer c ON s.customer_id = c.customer_id 
        JOIN employee e ON s.employee_id = e.employee_id
        JOIN pump p ON s.pump_id = p.pump_id
        JOIN fuel_type f ON p.fuel_id = f.fuel_id
        ORDER BY s.sale_date DESC 
        LIMIT 5
    ");
    $recentSales = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<h2>Petroleum Station Dashboard</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php else: ?>

<div class="dashboard-cards">
    <div class="card">
        <h3>Total Stations</h3>
        <div class="number"><?php echo $stats['stations']; ?></div>
    </div>
    <div class="card">
        <h3>Total Employees</h3>
        <div class="number"><?php echo $stats['employees']; ?></div>
    </div>
    <div class="card">
        <h3>Total Customers</h3>
        <div class="number"><?php echo $stats['customers']; ?></div>
    </div>
    <div class="card">
        <h3>Fuel Types</h3>
        <div class="number"><?php echo $stats['fuel_types']; ?></div>
    </div>
    <div class="card">
        <h3>Today's Sales</h3>
        <div class="number"><?php echo $todaySales['count']; ?></div>
        <div>Amount: RWF <?php echo number_format($todaySales['total'], 0); ?></div>
    </div>
</div>

<h3 style="margin-top: 30px;">Recent Sales</h3>
<?php if (count($recentSales) > 0): ?>
<table>
    <thead>
        <tr>
            <th>Sale ID</th>
            <th>Customer</th>
            <th>Employee</th>
            <th>Fuel Type</th>
            <th>Quantity (L)</th>
            <th>Total Amount</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recentSales as $sale): ?>
        <tr>
            <td><?php echo $sale['sale_id']; ?></td>
            <td><?php echo $sale['customer_name'] ?? 'Walk-in Customer'; ?></td>
            <td><?php echo $sale['employee_name']; ?></td>
            <td><?php echo $sale['fuel_name']; ?></td>
            <td><?php echo $sale['quantity']; ?> L</td>
            <td>RWF <?php echo number_format($sale['total_amount'], 0); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No sales records found.</p>
<?php endif; ?>

<div style="margin-top: 30px; padding: 20px; background-color: #f0f8ff; border-radius: 5px;">
    <h3>Database Connection Status</h3>
    <p style="color: green;">✓ Connected to: <strong><?php echo $dbname; ?></strong> database</p>
    <p>Server: <?php echo $host; ?></p>
    <p>Username: <?php echo $username; ?></p>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>