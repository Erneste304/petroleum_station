<?php
require_once 'includes/auth_middleware.php';
require_once 'config/database.php';

if (!isAdmin() && !isReceptionist()) {
    $_SESSION['error'] = "Access denied.";
    header("Location: index.php");
    exit();
}

include 'includes/header.php';

// Fetch some stats for receptionist
$customers_count = $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();
$recent_sales = $pdo->query("SELECT s.*, c.name as customer_name FROM sale s LEFT JOIN customer c ON s.customer_id = c.customer_id ORDER BY sale_date DESC LIMIT 5")->fetchAll();
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-headset text-primary me-2"></i> Receptionist Dashboard</h2>
        <p class="text-muted">Welcome to the Customer Care & Guidance portal.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="bi bi-cart-plus display-4 text-primary mb-3"></i>
                <h5 class="fw-bold">Record Sale</h5>
                <p class="small text-muted">Assist customers with fuel purchases and payments.</p>
                <a href="sales/create.php" class="btn btn-primary w-100 rounded-pill">New Sale</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="bi bi-person-plus display-4 text-success mb-3"></i>
                <h5 class="fw-bold">New Customer</h5>
                <p class="small text-muted">Register new customers to the loyalty program.</p>
                <a href="customers/create.php" class="btn btn-success w-100 rounded-pill">Register</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="bi bi-info-circle display-4 text-info mb-3"></i>
                <h5 class="fw-bold">Service Guide</h5>
                <p class="small text-muted">View available services and pricing information.</p>
                <a href="services.php" class="btn btn-info text-white w-100 rounded-pill">View Services</a>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h5 class="fw-bold mb-3">Recent Activity</h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td class="ps-4"><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in'); ?></td>
                        <td class="fw-bold">RWF <?php echo number_format($sale['total_amount'], 0); ?></td>
                        <td><?php echo date('d M, H:i', strtotime($sale['sale_date'])); ?></td>
                        <td><span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Completed</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
