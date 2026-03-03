<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_fuel'])) {
        $stmt = $pdo->prepare("INSERT INTO fuel_type (fuel_name, price_per_liter) VALUES (?, ?)");
        $stmt->execute([$_POST['fuel_name'], $_POST['price_per_liter']]);
        $success = "Fuel type added successfully!";
    }
}

// Fetch all fuel types
$fuel_types = $pdo->query("SELECT * FROM fuel_type")->fetchAll();
?>

<h2>Fuel Types Management</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h3>Add New Fuel Type</h3>
<form method="POST" class="form-group">
    <div class="form-group">
        <label>Fuel Name:</label>
        <input type="text" name="fuel_name" required>
    </div>
    <div class="form-group">
        <label>Price per Liter (RWF):</label>
        <input type="number" step="0.01" name="price_per_liter" required>
    </div>
    <button type="submit" name="add_fuel" class="btn">Add Fuel Type</button>
</form>

<h3>Fuel Types</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fuel Name</th>
            <th>Price per Liter</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fuel_types as $fuel): ?>
        <tr>
            <td><?php echo $fuel['fuel_id']; ?></td>
            <td><?php echo $fuel['fuel_name']; ?></td>
            <td>RWF <?php echo number_format($fuel['price_per_liter'], 0); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>