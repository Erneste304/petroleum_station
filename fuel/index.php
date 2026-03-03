<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';

// Handle delete request
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM fuel_type WHERE fuel_id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Fuel type deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Cannot delete fuel type: " . $e->getMessage();
    }
    header("Location: index.php");
    exit();
}

// Fetch all fuel types
$fuel_types = $pdo->query("SELECT * FROM fuel_type ORDER BY fuel_name")->fetchAll();
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-droplet"></i> Fuel Types Management</h2>
    </div>
    <div class="col-auto">
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Fuel Type
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> All Fuel Types
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fuel Name</th>
                        <th>Price per Liter</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fuel_types as $fuel): ?>
                    <tr>
                        <td><?php echo $fuel['fuel_id']; ?></td>
                        <td><?php echo htmlspecialchars($fuel['fuel_name']); ?></td>
                        <td>RWF <?php echo number_format($fuel['price_per_liter'], 0); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit.php?id=<?php echo $fuel['fuel_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?delete=<?php echo $fuel['fuel_id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this fuel type?')"
                                   title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>