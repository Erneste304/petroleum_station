<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_customer'])) {
        $stmt = $pdo->prepare("INSERT INTO customer (name, phone, vehicle_plate) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['vehicle_plate']]);
        $success = "Customer added successfully!";
    }
}

// Fetch all customers
$customers = $pdo->query("SELECT * FROM customer ORDER BY name")->fetchAll();
?>

<h2>Customers Management</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h3>Add New Customer</h3>
<form method="POST" class="form-group">
    <div class="form-group">
        <label>Full Name:</label>
        <input type="text" name="name" required>
    </div>
    <div class="form-group">
        <label>Phone:</label>
        <input type="text" name="phone" required>
    </div>
    <div class="form-group">
        <label>Vehicle Plate:</label>
        <input type="text" name="vehicle_plate">
    </div>
    <button type="submit" name="add_customer" class="btn">Add Customer</button>
</form>

<h3>Customer List</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Vehicle Plate</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $customer): ?>
        <tr>
            <td><?php echo $customer['customer_id']; ?></td>
            <td><?php echo $customer['name']; ?></td>
            <td><?php echo $customer['phone']; ?></td>
            <td><?php echo $customer['vehicle_plate'] ?? 'N/A'; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>