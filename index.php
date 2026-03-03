<?php
session_start();
// require authentication - redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
include 'includes/header.php';

// Get statistics
$stats = [];

try {
    // Total stations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM station");
    $stats['stations'] = $stmt->fetch()['count'];
    
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employee");
    $stats['employees'] = $stmt->fetch()['count'];
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer");
    $stats['customers'] = $stmt->fetch()['count'];
    
    // Total fuel types
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM fuel_type");
    $stats['fuel_types'] = $stmt->fetch()['count'];
    
    // Today's sales
    $stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sale WHERE DATE(sale_date) = CURDATE()");
    $todaySales = $stmt->fetch();
    
    // Monthly sales
    $stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sale WHERE MONTH(sale_date) = MONTH(CURDATE())");
    $monthlySales = $stmt->fetch();
    
    // Low stock alerts
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM tank t
        JOIN fuel_type f ON t.fuel_id = f.fuel_id
        WHERE (t.current_stock / t.capacity * 100) < 20
    ");
    $lowStock = $stmt->fetch()['count'];
    
    // Recent sales
    $stmt = $pdo->query("
        SELECT s.*, c.name as customer_name, 
               CONCAT(e.first_name, ' ', e.last_name) as employee_name,
               f.fuel_name
        FROM sale s 
        LEFT JOIN customer c ON s.customer_id = c.customer_id 
        JOIN employee e ON s.employee_id = e.employee_id
        JOIN pump p ON s.pump_id = p.pump_id
        JOIN fuel_type f ON p.fuel_id = f.fuel_id
        ORDER BY s.sale_date DESC 
        LIMIT 10
    ");
    $recentSales = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <p class="text-muted">Welcome to Petroleum Station Management System</p>
    </div>
    <div class="col-auto">
        <span class="badge bg-primary p-3">
            <i class="bi bi-calendar"></i> <?php echo date('F d, Y'); ?>
        </span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Stations</h6>
                        <h2 class="mb-0"><?php echo $stats['stations']; ?></h2>
                    </div>
                    <i class="bi bi-building fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Employees</h6>
                        <h2 class="mb-0"><?php echo $stats['employees']; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Customers</h6>
                        <h2 class="mb-0"><?php echo $stats['customers']; ?></h2>
                    </div>
                    <i class="bi bi-person-badge fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Fuel Types</h6>
                        <h2 class="mb-0"><?php echo $stats['fuel_types']; ?></h2>
                    </div>
                    <i class="bi bi-droplet fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Overview -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up"></i> Today's Sales
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <h3 class="text-primary"><?php echo $todaySales['count']; ?></h3>
                        <p class="text-muted">Transactions</p>
                    </div>
                    <div class="col-6 text-center">
                        <h3 class="text-success">RWF <?php echo number_format($todaySales['total'], 0); ?></h3>
                        <p class="text-muted">Revenue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-month"></i> Monthly Sales
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <h3 class="text-primary"><?php echo $monthlySales['count']; ?></h3>
                        <p class="text-muted">Transactions</p>
                    </div>
                    <div class="col-6 text-center">
                        <h3 class="text-success">RWF <?php echo number_format($monthlySales['total'], 0); ?></h3>
                        <p class="text-muted">Revenue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if ($lowStock > 0): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Low Stock Alert!</strong> There are <?php echo $lowStock; ?> fuel tanks with stock below 20%. 
    <a href="inventory.php" class="alert-link">View Inventory</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Recent Sales Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> Recent Sales</span>
        <a href="sales/create.php" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> New Sale
        </a>
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
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSales as $sale): ?>
                    <tr>
                        <td>#<?php echo $sale['sale_id']; ?></td>
                        <td><?php echo $sale['customer_name'] ?? 'Walk-in'; ?></td>
                        <td><?php echo $sale['employee_name']; ?></td>
                        <td><span class="badge bg-info"><?php echo $sale['fuel_name']; ?></span></td>
                        <td><?php echo $sale['quantity']; ?> L</td>
                        <td>RWF <?php echo number_format($sale['total_amount'], 0); ?></td>
                        <td><?php echo date('M d, H:i', strtotime($sale['sale_date'])); ?></td>
                        <td>
                            <a href="sales/view.php?id=<?php echo $sale['sale_id']; ?>" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <a href="sales/create.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-cart-plus"></i> New Sale
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="customers/create.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-person-plus"></i> Add Customer
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="employees/create.php" class="btn btn-outline-info w-100">
                            <i class="bi bi-person-workspace"></i> Add Employee
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="reports.php" class="btn btn-outline-warning w-100">
                            <i class="bi bi-file-text"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>