<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petroleum Station Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .status-bar {
            background-color: #28a745;
            color: white;
            padding: 5px 20px;
            text-align: right;
            font-size: 14px;
        }
        .status-bar.error {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php
    // Check database connection status
    $db_status = "Connected";
    $status_class = "";
    if (isset($pdo) && $pdo) {
        try {
            $pdo->query("SELECT 1");
        } catch (PDOException $e) {
            $db_status = "Disconnected";
            $status_class = "error";
        }
    } else {
        $db_status = "Disconnected";
        $status_class = "error";
    }
    ?>
    <div class="status-bar <?php echo $status_class; ?>">
        Database Status: <?php echo $db_status; ?> | 
        Database: petroleum_station_db
    </div>
    <div class="navbar">
        <h1>Petroleum Station Management System</h1>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="stations.php">Stations</a></li>
            <li><a href="employees.php">Employees</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="fuel.php">Fuel Types</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="sales.php">Sales</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
    </div>
    <div class="container">