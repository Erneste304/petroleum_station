<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petroleum Station Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- DataTables CSS for better tables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-fuel-pump"></i> Petroleum Station MS
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <?php if (hasPermission('stations')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-building"></i> Stations
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="../stations/index.php">View All</a></li>
                                <li><a class="dropdown-item" href="../stations/create.php">Add New</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('employees')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> Employees
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="../employees/index.php">View All</a></li>
                                <li><a class="dropdown-item" href="../employees/create.php">Add New</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('customers')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-badge"></i> Customers
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="../customers/index.php">View All</a></li>
                                <li><a class="dropdown-item" href="../customers/create.php">Add New</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('fuel')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-droplet"></i> Fuel
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="../fuel/index.php">View All</a></li>
                                <li><a class="dropdown-item" href="../fuel/create.php">Add New</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('sales')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-cart"></i> Sales
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="../sales/index.php">View All</a></li>
                                <li><a class="dropdown-item" href="../sales/create.php">New Sale</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (isCustomer()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../fuel_purchase.php">
                                <i class="bi bi-droplet-half"></i> Buy Fuel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../my_purchases.php">
                                <i class="bi bi-clock-history"></i> My Purchases
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): 
                        // Fetch user profile photo if not already defined
                        $header_stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
                        $header_stmt->execute([$_SESSION['user_id']]);
                        $header_user = $header_stmt->fetch();
                        $profile_photo = $header_user['profile_photo'] ?? null;
                    ?>
                        <!-- Services Dropdown -->
                        <?php if (hasPermission('fuel_delivery') || hasPermission('car_wash') || hasPermission('loyalty')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-grid"></i> Services
                            </a>
                            <ul class="dropdown-menu shadow">
                                <?php if (hasPermission('fuel_delivery')): ?>
                                <li><a class="dropdown-item" href="../fuel_delivery.php"><i class="bi bi-truck me-2 text-primary"></i> Fuel Delivery</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="../loyalty.php"><i class="bi bi-gift me-2 text-warning"></i> Loyalty & Rewards</a></li>
                                <?php if (hasPermission('car_wash')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Partner Services</h6></li>
                                <li><a class="dropdown-item" href="../car_wash.php"><i class="bi bi-car-front me-2 text-info"></i> Car Detailing</a></li>
                                <li><a class="dropdown-item" href="../partner_shares.php"><i class="bi bi-graph-up-arrow me-2 text-warning"></i> Buy Shares</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Role-Specific Links -->
                        <?php if (isReceptionist()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../receptionist_dashboard.php"><i class="bi bi-headset"></i> Receptionist</a>
                            </li>
                        <?php endif; ?>

                        <?php if (isAccountant()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../accountant_dashboard.php"><i class="bi bi-wallet2"></i> Finances</a>
                            </li>
                        <?php endif; ?>

                        <?php if (isAdmin()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-shield-check"></i> Admin
                                </a>
                                <ul class="dropdown-menu shadow border-0">
                                    <li><a class="dropdown-item" href="../admin_finance_mgm.php">Finance Auth</a></li>
                                    <li><a class="dropdown-item" href="../admin_payroll_approval.php">Payroll Approval</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Unified User Dropdown -->
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1 mt-1 mt-lg-0" href="#" role="button" data-bs-toggle="dropdown">
                                <?php if ($profile_photo): ?>
                                    <img src="../img/profiles/<?php echo $profile_photo; ?>" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi bi-person-circle me-2"></i>
                                <?php endif; ?>
                                <span class="small fw-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 15px; overflow: hidden;">
                                <li><h6 class="dropdown-header border-bottom pb-2">User Settings</h6></li>
                                <li><a class="dropdown-item py-2" href="../profile.php"><i class="bi bi-person-badge me-2 text-primary"></i> My Profile</a></li>
                                <li><a class="dropdown-item py-2" href="../settings.php"><i class="bi bi-gear me-2 text-info"></i> Account Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 text-danger fw-bold" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-light rounded-pill px-4 btn-sm fw-bold my-1" href="../login.php">
                                Login <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container mt-4">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error'];
                                                            unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>