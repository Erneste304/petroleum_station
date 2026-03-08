<?php
require_once 'includes/auth_middleware.php';
require_once 'config/database.php';
requirePermission('car_wash');

// Prepare variables for the view
$services = [];
$myBookings = [];
$error = '';
$success = '';

$customerId = $_SESSION['customer_id'] ?? null;

if (!$customerId) {
    if (isAdmin()) {
        $error = "As an admin, you can view this page but cannot book a car wash without a customer profile.";
    } else {
        $error = "Customer profile not found. Please contact support.";
    }
} else {
    // 1. Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_wash'])) {
        $serviceId = $_POST['service_id'] ?? '';
        $bookingDate = $_POST['booking_date'] ?? '';
        $bookingTime = $_POST['booking_time'] ?? '';
        $vehiclePlate = trim($_POST['vehicle_plate'] ?? '');

        if (empty($serviceId) || empty($bookingDate) || empty($bookingTime) || empty($vehiclePlate)) {
            $error = 'All booking fields are required.';
        } else {
            // Check if booking date is in the future
            if (strtotime($bookingDate) < strtotime('today')) {
                $error = 'Booking date cannot be in the past.';
            } else {
                try {
                    $stmt = $pdo->prepare('
                        INSERT INTO car_wash_booking (customer_id, service_id, booking_date, booking_time, vehicle_plate) 
                        VALUES (?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([$customerId, $serviceId, $bookingDate, $bookingTime, $vehiclePlate]);
                    $success = 'Your car wash has been scheduled successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to schedule car wash. Please try again later.';
                    // log $e->getMessage() in a real app
                }
            }
        }
    }

    // 2. Fetch Available Services
    try {
        $stmt = $pdo->query('SELECT * FROM car_wash_service ORDER BY price ASC');
        $services = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Failed to load services.';
    }

    // 3. Fetch User's Upcoming/Past Bookings
    try {
        $stmt = $pdo->prepare('
            SELECT b.*, s.name as service_name, s.price 
            FROM car_wash_booking b
            JOIN car_wash_service s ON b.service_id = s.service_id
            WHERE b.customer_id = ?
            ORDER BY b.booking_date DESC, b.booking_time DESC
        ');
        $stmt->execute([$customerId]);
        $myBookings = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Handle error silently or show
    }
}

// Include header after logic so alerts can be displayed
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-car-front text-info me-2"></i> Partner Car Wash & Detailing</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Car Wash</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($customerId): ?>
    <div class="row">
        <!-- New Booking Form Column -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary"><i class="bi bi-calendar-plus me-2"></i> Schedule a Wash</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="car_wash.php">
                        <input type="hidden" name="book_wash" value="1">
                        
                        <div class="mb-3">
                            <label for="service_id" class="form-label fw-bold">Select Service</label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="" disabled selected>Choose a package...</option>
                                <?php foreach ($services as $srv): ?>
                                    <option value="<?php echo $srv['service_id']; ?>">
                                        <?php echo htmlspecialchars($srv['name']); ?> - RWF <?php echo number_format($srv['price'], 0); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="booking_date" class="form-label fw-bold">Date</label>
                                <input type="date" class="form-control" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="booking_time" class="form-label fw-bold">Time</label>
                                <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="vehicle_plate" class="form-label fw-bold">Vehicle Plate Number</label>
                            <input type="text" class="form-control" id="vehicle_plate" name="vehicle_plate" placeholder="e.g. RAA 123 A" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Confirm Booking <i class="bi bi-check2-circle ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Available Services Info Column -->
        <div class="col-lg-7 mb-4">
            <div class="row g-3">
                <?php foreach ($services as $srv): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($srv['name']); ?></h6>
                                    <span class="badge bg-success rounded-pill">RWF <?php echo number_format($srv['price'], 0); ?></span>
                                </div>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($srv['description']); ?></p>
                                <div class="text-muted small mt-auto">
                                    <i class="bi bi-clock me-1"></i> Est. <?php echo $srv['estimated_duration_minutes']; ?> mins
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Booking History Table -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> My Bookings</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($myBookings)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-calendar-x fs-2 mb-2 d-block opacity-50"></i>
                            <p class="mb-0">You have no car wash bookings yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Vehicle</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myBookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></div>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($booking['booking_time'])); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($booking['vehicle_plate']); ?></span></td>
                                            <td>
                                                <?php 
                                                    $statusClass = 'bg-warning text-dark';
                                                    if ($booking['status'] === 'Confirmed') $statusClass = 'bg-info bg-opacity-25 text-info border border-info';
                                                    if ($booking['status'] === 'Completed') $statusClass = 'bg-success';
                                                    if ($booking['status'] === 'Cancelled') $statusClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($booking['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>