<?php
require_once '../includes/auth_middleware.php';
requireAdmin();
require_once '../config/database.php';
include '../includes/header.php';

// Handle delete request
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM customer WHERE customer_id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Customer deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Cannot delete customer: " . $e->getMessage();
    }
    header("Location: index.php");
    exit();
}

// Fetch all customers
$customers = $pdo->query("SELECT * FROM customer ORDER BY name")->fetchAll();
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-person-badge"></i> Customers Management</h2>
    </div>
    <div class="col-auto">
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Customer
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> All Customers
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Vehicle Plate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['customer_id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['vehicle_plate'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit.php?id=<?php echo $customer['customer_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?delete=<?php echo $customer['customer_id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this customer?')"
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
