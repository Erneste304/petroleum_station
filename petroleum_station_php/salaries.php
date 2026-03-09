<?php
require_once 'includes/auth_middleware.php';
require_once 'config/database.php';

if (!isAdmin() && !isFinancePermitted('Salaries')) {
    $_SESSION['error'] = "Access denied. Action requires Admin-granted financial permission.";
    header("Location: accountant_dashboard.php");
    exit();
}

// Handle payment
if (isset($_POST['pay_salary'])) {
    $emp_id = $_POST['employee_id'];
    $amount = $_POST['amount'];
    
    // In a real system, we'd have a salary_payments table. 
    // For this simulation, we'll just log it as a successful operation.
    $_SESSION['success'] = "Salary payment of RWF " . number_format($amount, 0) . " processed successfully.";
}

$employees = $pdo->query("SELECT * FROM employee ORDER BY first_name")->fetchAll();

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-person-check text-success me-2"></i> Payroll Management</h2>
        <p class="text-muted">Process staff salaries and manage financial reports.</p>
    </div>
    <div class="col-auto">
        <a href="accountant_dashboard.php" class="btn btn-secondary rounded-pill">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($employees as $emp): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light p-3 rounded-circle me-3">
                        <i class="bi bi-person-fill text-primary h4 mb-0"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($emp['position']); ?></small>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="employee_id" value="<?php echo $emp['employee_id']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Amount to Pay (RWF)</label>
                        <input type="number" name="amount" class="form-control" placeholder="Enter salary amount" required>
                    </div>
                    <button type="submit" name="pay_salary" class="btn btn-primary w-100 rounded-pill fw-bold">
                        <i class="bi bi-cash-coin me-1"></i> Pay Now
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
