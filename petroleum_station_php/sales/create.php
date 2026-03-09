require_once '../includes/auth_middleware.php';
require_once '../config/database.php';
requirePermission('sales');
include '../includes/header.php';

// Fetch data for dropdowns
$customers = $pdo->query("SELECT * FROM customer ORDER BY name")->fetchAll();
$employees = $pdo->query("SELECT * FROM employee ORDER BY first_name")->fetchAll();

// Fetch pumps with fuel details and current stock
$pumps = $pdo->query("
    SELECT p.*, f.fuel_name, f.price_per_liter, 
           t.current_stock, t.tank_id
    FROM pump p
    JOIN fuel_type f ON p.fuel_id = f.fuel_id
    JOIN tank t ON f.fuel_id = t.fuel_id
    WHERE t.current_stock > 0
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Get pump details for price
        $stmt = $pdo->prepare("
            SELECT f.price_per_liter, f.fuel_id 
            FROM pump p
            JOIN fuel_type f ON p.fuel_id = f.fuel_id
            WHERE p.pump_id = ?
        ");
        $stmt->execute([$_POST['pump_id']]);
        $pump = $stmt->fetch();
        
        $total_amount = $_POST['quantity'] * $pump['price_per_liter'];
        
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
            $total_amount
        ]);
        
        $sale_id = $pdo->lastInsertId();
        
        // Insert payment
        $stmt = $pdo->prepare("INSERT INTO payment (sale_id, payment_method) VALUES (?, ?)");
        $stmt->execute([$sale_id, $_POST['payment_method']]);
        
        // Update tank stock
        $stmt = $pdo->prepare("
            UPDATE tank t 
            JOIN pump p ON t.fuel_id = p.fuel_id 
            SET t.current_stock = t.current_stock - ? 
            WHERE p.pump_id = ?
        ");
        $stmt->execute([$_POST['quantity'], $_POST['pump_id']]);

        // If this sale was from a request, mark the request as completed
        if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
            $stmt = $pdo->prepare("UPDATE fuel_request SET status = 'Completed' WHERE request_id = ?");
            $stmt->execute([$_POST['request_id']]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Sale recorded successfully!";
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error recording sale: " . $e->getMessage();
    }
}

// Handle pre-filled request data
$pre_request_id = $_GET['request_id'] ?? '';
$pre_customer_id = $_GET['customer_id'] ?? '';
$pre_fuel_id = $_GET['fuel_id'] ?? '';
$pre_qty = $_GET['qty'] ?? '';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-cart-plus"></i> Record New Sale</h2>
    </div>
    <div class="col-auto">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Sales
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-receipt"></i> Sale Information
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="saleForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" id="customer_id">
                                <option value="">Walk-in Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['customer_id']; ?>" <?php echo ($pre_customer_id == $customer['customer_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">Employee *</label>
                            <select class="form-select" name="employee_id" id="employee_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['employee_id']; ?>">
                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pump_id" class="form-label">Fuel Type *</label>
                            <select class="form-select" name="pump_id" id="pump_id" required onchange="updatePrice()">
                                <option value="">Select Fuel</option>
                                <?php foreach ($pumps as $pump): ?>
                                <option value="<?php echo $pump['pump_id']; ?>" 
                                        data-price="<?php echo $pump['price_per_liter']; ?>"
                                        data-stock="<?php echo $pump['current_stock']; ?>"
                                        <?php echo ($pre_fuel_id == $pump['fuel_id']) ? 'selected' : ''; ?>>
                                    <?php echo $pump['fuel_name']; ?> - RWF <?php echo number_format($pump['price_per_liter'], 0); ?>/L 
                                    (Stock: <?php echo $pump['current_stock']; ?> L)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity (Liters) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" 
                                   id="quantity" name="quantity" required onchange="calculateTotal()"
                                   value="<?php echo htmlspecialchars($pre_qty); ?>">
                            <small class="text-muted" id="stockWarning"></small>
                        </div>
                    </div>
                    
                    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($pre_request_id); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_per_liter" class="form-label">Price per Liter (RWF)</label>
                            <input type="text" class="form-control" id="price_per_liter" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="total_amount" class="form-label">Total Amount (RWF)</label>
                            <input type="text" class="form-control" id="total_amount" readonly>
                            <input type="hidden" name="total_amount" id="total_amount_hidden">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="Cash">Cash</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Card">Credit/Debit Card</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Complete Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updatePrice() {
    const pumpSelect = document.getElementById('pump_id');
    const selectedOption = pumpSelect.options[pumpSelect.selectedIndex];
    const price = selectedOption.dataset.price;
    const stock = selectedOption.dataset.stock;
    
    document.getElementById('price_per_liter').value = price ? 'RWF ' + Number(price).toLocaleString() : '';
    document.getElementById('stockWarning').innerHTML = stock ? 'Available stock: ' + stock + ' L' : '';
    
    calculateTotal();
}

function calculateTotal() {
    const pumpSelect = document.getElementById('pump_id');
    const quantity = document.getElementById('quantity').value;
    const selectedOption = pumpSelect.options[pumpSelect.selectedIndex];
    const price = selectedOption.dataset.price;
    const stock = selectedOption.dataset.stock;
    
    // Validate quantity against stock
    if (stock && quantity > parseFloat(stock)) {
        alert('Warning: Quantity exceeds available stock!');
    }
    
    if (price && quantity) {
        const total = quantity * price;
        document.getElementById('total_amount').value = 'RWF ' + total.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('total_amount_hidden').value = total;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
});
</script>

<?php include '../includes/footer.php'; ?>
