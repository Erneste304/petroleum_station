<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';

// Fetch stations for dropdown
$stations = $pdo->query("SELECT * FROM station ORDER BY station_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO employee (first_name, last_name, position, phone, station_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['position'], $_POST['phone'], $_POST['station_id']]);
        $_SESSION['success'] = "Employee added successfully!";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-plus-circle"></i> Add New Employee</h2>
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
                <i class="bi bi-person-badge"></i> Employee Information
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="position" class="form-label">Position *</label>
                        <select class="form-select" id="position" name="position" required>
                            <option value="">Select Position</option>
                            <option value="Manager">Manager</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Pump Attendant">Pump Attendant</option>
                            <option value="Supervisor">Supervisor</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="station_id" class="form-label">Station *</label>
                        <select class="form-select" id="station_id" name="station_id" required>
                            <option value="">Select Station</option>
                            <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['station_id']; ?>">
                                <?php echo htmlspecialchars($station['station_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>