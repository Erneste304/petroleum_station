<?php
require_once 'config/database.php';
include 'includes/header.php';

// Fetch all sales with details
$sales = $pdo->query("
    SELECT s.*, c.name as customer_name, 
           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           p.pump_id, f.fuel_name
    FROM sale s
    LEFT JOIN customer c ON s.customer_id = c.customer_id
    JOIN employee e ON s.employee_id = e.employee_id
    JOIN pump p ON s.pump_id = p.pump_id
    JOIN fuel_type f ON p.fuel_id = f.fuel_id
    ORDER BY s.sale_date DESC
")->fetchAll();
?>

<h2>Sales Records</h2>

<a href="add_sale.php" class="btn">Add New Sale</a>

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
        <?php foreach ($sales as $sale): ?>
        <tr>
            <td><?php echo $sale['sale_id']; ?></td>
            <td><?php echo $sale['customer_name'] ?? 'N/A'; ?></td>
            <td><?php echo $sale['employee_name']; ?></td>
            <td><?php echo $sale['fuel_name']; ?></td>
            <td><?php echo $sale['quantity']; ?></td>
            <td>RWF <?php echo number_format($sale['total_amount'], 0); ?></td>
            <td><?php echo $sale['sale_date']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>