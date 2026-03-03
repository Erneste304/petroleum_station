<?php
require_once 'config/database.php';
include 'includes/header.php';

// Fetch data for dropdowns
$customers = $pdo->query("SELECT * FROM customer ORDER BY name")->fetchAll();
$employees = $pdo->query("SELECT * FROM employee ORDER BY first_name")->fetchAll();
$pumps = $pdo->query("
    SELECT p.*, f.fuel_name, f.price_per_liter 
    FROM pump p
    JOIN fuel_type f ON p.fuel_id = f.fuel_id
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Insert sale
        $stmt = $pdo->prepare("
            INSERT INTO sale (customer_id, employee_id, pump_id, quantity, total_amount) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['customer_id'] ?: null,
            $_POST['employee_id'],
            $_POST['pump_id'],
            $_POST['quantity'],
            $_POST['total_amount']
        ]);
        
        $sale_id = $pdo->lastInsertId();
        
        // Insert payment
        $stmt = $pdo->prepare("INSERT INTO payment (sale_id, payment_method) VALUES (?, ?)");
        $stmt->execute([$sale_id, $_POST['payment_method']]);
        
        // Update tank stock (simplified - in reality you'd need to know which tank)
        $stmt = $pdo->prepare("
            UPDATE tank t 
            JOIN pump p ON t.fuel_id = p.fuel_id 
            SET t.current_stock = t.current_stock - ? 
            WHERE p.pump_id = ?
        ");
        $stmt->execute([$_POST['quantity'], $_POST['pump_id']]);
        
        $pdo->commit();
        $success = "Sale recorded successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error recording sale: " . $e->getMessage();
    }
}
?>

<h2>Add New Sale</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" class="form-group" id="saleForm">
    <div class="form-group">
        <label>Customer:</label>
        <select name="customer_id">
            <option value="">Walk-in Customer</option>
            <?php foreach ($customers as $customer): ?>
            <option value="<?php echo $customer['customer_id']; ?>"><?php echo $customer['name']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Employee:</label>
        <select name="employee_id" required>
            <option value="">Select Employee</option>
            <?php foreach ($employees as $employee): ?>
            <option value="<?php echo $employee['employee_id']; ?>"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Pump/Fuel Type:</label>
        <select name="pump_id" required id="pumpSelect">
            <option value="">Select Pump</option>
            <?php foreach ($pumps as $pump): ?>
            <option value="<?php echo $pump['pump_id']; ?>" data-price="<?php echo $pump['price_per_liter']; ?>">
                Pump <?php echo $pump['pump_id']; ?> - <?php echo $pump['fuel_name']; ?> (RWF <?php echo $pump['price_per_liter']; ?>/L)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Quantity (Liters):</label>
        <input type="number" step="0.01" name="quantity" required id="quantity" onchange="calculateTotal()">
    </div>
    
    <div class="form-group">
        <label>Total Amount (RWF):</label>
        <input type="number" step="0.01" name="total_amount" required id="total_amount" readonly>
    </div>
    
    <div class="form-group">
        <label>Payment Method:</label>
        <select name="payment_method" required>
            <option value="Cash">Cash</option>
            <option value="Mobile Money">Mobile Money</option>
            <option value="Card">Card</option>
        </select>
    </div>
    
    <button type="submit" class="btn">Record Sale</button>
</form>

<script>
function calculateTotal() {
    const pumpSelect = document.getElementById('pumpSelect');
    const quantity = document.getElementById('quantity').value;
    
    if (pumpSelect.selectedIndex > 0 && quantity) {
        const price = pumpSelect.options[pumpSelect.selectedIndex].dataset.price;
        document.getElementById('total_amount').value = quantity * price;
    }
}

document.getElementById('pumpSelect').addEventListener('change', calculateTotal);
</script>

<?php include 'includes/footer.php'; ?>