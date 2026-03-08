<?php
require_once '../includes/auth_middleware.php';
require_once '../config/database.php';
requirePermission('sales');

// Handle delete request
if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();
        
        // Get sale details to restore stock
        $stmt = $pdo->prepare("SELECT pump_id, quantity FROM sale WHERE sale_id = ?");
        $stmt->execute([$_GET['delete']]);
        $sale = $stmt->fetch();
        
        if ($sale) {
            // Restore tank stock
            $stmt = $pdo->prepare("
                UPDATE tank t 
                JOIN pump p ON t.fuel_id = p.fuel_id 
                SET t.current_stock = t.current_stock + ? 
                WHERE p.pump_id = ?
            ");
            $stmt->execute([$sale['quantity'], $sale['pump_id']]);
            
            // Delete the sale (cascades to payment)
            $stmt = $pdo->prepare("DELETE FROM sale WHERE sale_id = ?");
            $stmt->execute([$_GET['delete']]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Sale deleted and stock restored!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting sale: " . $e->getMessage();
    }
    header("Location: index.php");
    exit();
}

include '../includes/header.php';

// Fetch all sales with details
$sales = $pdo->query("
    SELECT s.*, c.name as customer_name, 
           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           f.fuel_name,
           p.payment_method
    FROM sale s
    LEFT JOIN customer c ON s.customer_id = c.customer_id
    JOIN employee e ON s.employee_id = e.employee_id
    JOIN pump pu ON s.pump_id = pu.pump_id
    JOIN fuel_type f ON pu.fuel_id = f.fuel_id
    LEFT JOIN payment p ON s.sale_id = p.sale_id
    ORDER BY s.sale_date DESC
")->fetchAll();
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-cart"></i> Sales Management</h2>
    </div>
    <div class="col-auto">
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Sale
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> All Sales Records
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Employee</th>
                        <th>Fuel Type</th>
                        <th>Quantity</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td>#<?php echo $sale['sale_id']; ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></td>
                        <td><?php echo htmlspecialchars($sale['employee_name']); ?></td>
                        <td><span class="badge bg-info"><?php echo $sale['fuel_name']; ?></span></td>
                        <td><?php echo $sale['quantity']; ?> L</td>
                        <td>RWF <?php echo number_format($sale['total_amount'], 0); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $sale['payment_method'] == 'Cash' ? 'success' : 
                                    ($sale['payment_method'] == 'Mobile Money' ? 'warning' : 'primary'); 
                            ?>">
                                <?php echo $sale['payment_method']; ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view.php?id=<?php echo $sale['sale_id']; ?>" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $sale['sale_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?delete=<?php echo $sale['sale_id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this sale? This will restore stock.')"
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
