<?php
require_once 'includes/auth_middleware.php';
require_once 'config/database.php';

if (!isAdmin() && !isAccountant()) {
    $_SESSION['error'] = "Access denied.";
    header("Location: index.php");
    exit();
}

// Handle permission request
if (isset($_POST['request_permission'])) {
    $stmt = $pdo->prepare("INSERT INTO finance_permission_request (accountant_id, module_name, status) VALUES (?, ?, 'Pending')");
    $stmt->execute([$_SESSION['user_id'], $_POST['module']]);
    $_SESSION['success'] = "Permission request sent to Admin.";
}

// Fetch active permissions
$active_perms = $pdo->prepare("SELECT * FROM finance_permission_request WHERE accountant_id = ? AND status = 'Granted' AND (expiry IS NULL OR expiry > NOW())");
$active_perms->execute([$_SESSION['user_id']]);
$current_perms = $active_perms->fetchAll(PDO::FETCH_COLUMN, 3); // Get module_names

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-wallet2 text-primary"></i> Accountant Dashboard</h2>
        <p class="text-muted">Manage company finances, payroll, and audit logs.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Salaries Module -->
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <i class="bi bi-cash-stack display-4 text-success mb-3"></i>
                <h4 class="fw-bold">Employee Salaries</h4>
                <p class="text-muted">Process monthly payroll and generate salary reports.</p>
                
                <?php if (in_array('Salaries', $current_perms) || isAdmin()): ?>
                    <a href="salaries.php" class="btn btn-success px-4 rounded-pill">
                        <i class="bi bi-play-circle me-1"></i> Process Payroll
                    </a>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="module" value="Salaries">
                        <button type="submit" name="request_permission" class="btn btn-outline-primary px-4 rounded-pill">
                            <i class="bi bi-lock-fill me-1"></i> Request Access
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Audit Module -->
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <i class="bi bi-search display-4 text-info mb-3"></i>
                <h4 class="fw-bold">Transaction Audit</h4>
                <p class="text-muted">Monitor staff approvals and verify transaction accuracy.</p>
                <a href="audit_logs.php" class="btn btn-info text-white px-4 rounded-pill">
                    <i class="bi bi-file-earmark-text me-1"></i> View Logs
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h5 class="fw-bold mb-3">Recent Financial Requests</h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Action</th>
                        <th>Requested On</th>
                        <th>Status</th>
                        <th>Admin Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $reqs = $pdo->prepare("SELECT * FROM finance_permission_request WHERE accountant_id = ? ORDER BY created_at DESC LIMIT 5");
                    $reqs->execute([$_SESSION['user_id']]);
                    foreach ($reqs->fetchAll() as $r):
                    ?>
                    <tr>
                        <td class="ps-4"><?php echo $r['module_name']; ?></td>
                        <td><?php echo date('d M, H:i', strtotime($r['created_at'])); ?></td>
                        <td>
                            <span class="badge rounded-pill <?php echo $r['status'] == 'Granted' ? 'bg-success' : ($r['status'] == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                <?php echo $r['status']; ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?php echo $r['status'] == 'Granted' ? 'Expiry: ' . ($r['expiry'] ?? 'Never') : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
