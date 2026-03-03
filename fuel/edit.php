<?php
require_once '../includes/auth_middleware.php';
requireAdmin();
require_once '../config/database.php';
include '../includes/header.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No fuel ID provided";
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Fetch fuel data
$stmt = $pdo->prepare("SELECT * FROM fuel_type WHERE fuel_id = ?");
$stmt->execute([$id]);
$fuel = $stmt->fetch();

if (!$fuel) {
    $_SESSION['error'] = "Fuel type not found";
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE fuel_type SET fuel_name = ?, price_per_liter = ? WHERE fuel_id = ?");
        $stmt->execute([$_POST['fuel_name'], $_POST['price_per_liter'], $id]);
        $_SESSION['success'] = "Fuel type updated successfully!";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-pencil"></i> Edit Fuel Type</h2>
    </div>
    <div class="col-auto">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-droplet"></i> Edit Fuel Type Information
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="fuel_name" class="form-label">Fuel Name *</label>
                        <select class="form-select" id="fuel_name" name="fuel_name" required>
                            <option value="">Select Fuel Type</option>
                            <option value="Petrol" <?php echo $fuel['fuel_name'] == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                            <option value="Diesel" <?php echo $fuel['fuel_name'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="Super" <?php echo $fuel['fuel_name'] == 'Super' ? 'selected' : ''; ?>>Super</option>
                            <option value="Kerosene" <?php echo $fuel['fuel_name'] == 'Kerosene' ? 'selected' : ''; ?>>Kerosene</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price_per_liter" class="form-label">Price per Liter (RWF) *</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="price_per_liter" name="price_per_liter" 
                               value="<?php echo $fuel['price_per_liter']; ?>" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Fuel Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
