<?php
require_once 'config/database.php';
include 'includes/header.php';

// Get date range from request or default to current month
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Sales by fuel type
$stmt = $pdo->prepare("
    SELECT f.fuel_name, 
           COUNT(*) as sale_count,
           SUM(s.quantity) as total_liters,
           SUM(s.total_amount) as total_revenue
    FROM sale s
    JOIN pump p ON s.pump_id = p.pump_id
    JOIN fuel_type f ON p.fuel_id = f.fuel_id
    WHERE DATE(s.sale_date) BETWEEN ? AND ?
    GROUP BY f.fuel_id, f.fuel_name
");
$stmt->execute([$start_date, $end_date]);
$fuel_sales = $stmt->fetchAll();

// Top customers
$stmt = $pdo->prepare("
    SELECT c.name, c.phone, 
           COUNT(*) as purchase_count,
           SUM(s.total_amount) as total_spent
    FROM sale s
    JOIN customer c ON s.customer_id = c.customer_id
    WHERE DATE(s.sale_date) BETWEEN ? AND ?
    GROUP BY c.customer_id, c.name, c.phone
    ORDER BY total_spent DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$top_customers = $stmt->fetchAll();

// Daily sales summary
$stmt = $pdo->prepare("
    SELECT DATE(sale_date) as sale_day,
           COUNT(*) as transaction_count,
           SUM(quantity) as total_liters,
           SUM(total_amount) as daily_revenue
    FROM sale
    WHERE DATE(sale_date) BETWEEN ? AND ?
    GROUP BY DATE(sale_date)
    ORDER BY sale_day DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();

// Overall summary
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_transactions,
           COALESCE(SUM(quantity), 0) as total_liters,
           COALESCE(SUM(total_amount), 0) as total_revenue
    FROM sale
    WHERE DATE(sale_date) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();
?>

<h2>Sales Reports</h2>

<form method="GET" class="form-group" style="display: flex; gap: 10px; align-items: flex-end;">
    <div>
        <label>Start Date:</label>
        <input type="date" name="start_date" value="<?php echo $start_date; ?>">
    </div>
    <div>
        <label>End Date:</label>
        <input type="date" name="end_date" value="<?php echo $end_date; ?>">
    </div>
    <button type="submit" class="btn">Generate Report</button>
</form>

<div class="dashboard-cards">
    <div class="card">
        <h3>Total Transactions</h3>
        <div class="number"><?php echo $summary['total_transactions']; ?></div>
    </div>
    <div class="card">
        <h3>Total Liters Sold</h3>
        <div class="number"><?php echo number_format($summary['total_liters'], 0); ?> L</div>
    </div>
    <div class="card">
        <h3>Total Revenue</h3>
        <div class="number">RWF <?php echo number_format($summary['total_revenue'], 0); ?></div>
    </div>
</div>

<h3 style="margin-top: 30px;">Sales by Fuel Type</h3>
<table>
    <thead>
        <tr>
            <th>Fuel Type</th>
            <th>Number of Sales</th>
            <th>Total Liters</th>
            <th>Total Revenue</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fuel_sales as $fuel): ?>
        <tr>
            <td><?php echo $fuel['fuel_name']; ?></td>
            <td><?php echo $fuel['sale_count']; ?></td>
            <td><?php echo number_format($fuel['total_liters'], 0); ?> L</td>
            <td>RWF <?php echo number_format($fuel['total_revenue'], 0); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="margin-top: 30px;">Top Customers</h3>
<table>
    <thead>
        <tr>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Purchase Count</th>
            <th>Total Spent</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($top_customers as $customer): ?>
        <tr>
            <td><?php echo $customer['name']; ?></td>
            <td><?php echo $customer['phone']; ?></td>
            <td><?php echo $customer['purchase_count']; ?></td>
            <td>RWF <?php echo number_format($customer['total_spent'], 0); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="margin-top: 30px;">Daily Sales Summary</h3>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Transactions</th>
            <th>Liters Sold</th>
            <th>Daily Revenue</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($daily_sales as $day): ?>
        <tr>
            <td><?php echo $day['sale_day']; ?></td>
            <td><?php echo $day['transaction_count']; ?></td>
            <td><?php echo number_format($day['total_liters'], 0); ?> L</td>
            <td>RWF <?php echo number_format($day['daily_revenue'], 0); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>