<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_station'])) {
        $stmt = $pdo->prepare("INSERT INTO station (station_name, location, phone) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['station_name'], $_POST['location'], $_POST['phone']]);
        $success = "Station added successfully!";
    }
}

// Fetch all stations
$stations = $pdo->query("SELECT * FROM station")->fetchAll();
?>

<h2>Stations Management</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h3>Add New Station</h3>
<form method="POST" class="form-group">
    <div class="form-group">
        <label>Station Name:</label>
        <input type="text" name="station_name" required>
    </div>
    <div class="form-group">
        <label>Location:</label>
        <input type="text" name="location" required>
    </div>
    <div class="form-group">
        <label>Phone:</label>
        <input type="text" name="phone" required>
    </div>
    <button type="submit" name="add_station" class="btn">Add Station</button>
</form>

<h3>Existing Stations</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Location</th>
            <th>Phone</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stations as $station): ?>
        <tr>
            <td><?php echo $station['station_id']; ?></td>
            <td><?php echo $station['station_name']; ?></td>
            <td><?php echo $station['location']; ?></td>
            <td><?php echo $station['phone']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>