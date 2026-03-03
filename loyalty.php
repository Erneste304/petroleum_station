<?php
require_once 'includes/auth_middleware.php';
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-gift text-warning me-2"></i> Loyalty & Rewards</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Loyalty</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 text-center">
        <div class="p-5 bg-white rounded-3 shadow-sm border">
            <div class="mb-4">
                <i class="bi bi-star-half text-warning opacity-75" style="font-size: 4rem;"></i>
            </div>
            <h3 class="fw-bold mb-3">Rewards Program Launching Soon</h3>
            <p class="lead text-muted mb-4">
                We are actively tallying your purchases behind the scenes! Our official points dashboard and redemption catalog will be launching very soon.
            </p>
            <a href="index.php" class="btn btn-primary px-4 py-2">
                <i class="bi bi-arrow-left me-2"></i> Return to Dashboard
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>