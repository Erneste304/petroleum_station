<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_employee'])) {
        $stmt = $pdo->prepare("INSERT INTO employee (first_name, last_name, position, phone, station_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['position'], $_POST['phone'], $_POST['station_id']]);
        $success = "Employee added successfully!";
    }
}

// Fetch all employees with station names
$employees = $pdo->query("
    SELECT e.*, s.station_name 
    FROM employee e 
    LEFT JOIN station s ON e.station_id = s.station_id
")->fetchAll();

// Fetch stations for dropdown
$stations = $pdo->query("SELECT * FROM station")->fetchAll();
?>

<h2>Employees Management</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h3>Add New Employee</h3>
<form method="POST" class="form-group">
    <div class="form-group">
        <label>First Name:</label>
        <input type="text" name="first_name" required>
    </div>
    <div class="form-group">
        <label>Last Name:</label>
        <input type="text" name="last_name" required>
    </div>
    <div class="form-group">
        <label>Position:</label>
        <input type="text" name="position" required>
    </div>
    <div class="form-group">
        <label>Phone:</label>
        <input type="text" name="phone" required>
    </div>
    <div class="form-group">
        <label>Station:</label>
        <select name="station_id" required>
            <option value="">Select Station</option>
            <?php foreach ($stations as $station): ?>
            <option value="<?php echo $station['station_id']; ?>"><?php echo $station['station_name']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" name="add_employee" class="btn">Add Employee</button>
</form>

<h3>Employee List</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Phone</th>
            <th>Station</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $employee): ?>
        <tr>
            <td><?php echo $employee['employee_id']; ?></td>
            <td><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></td>
            <td><?php echo $employee['position']; ?></td>
            <td><?php echo $employee['phone']; ?></td>
            <td><?php echo $employee['station_name'] ?? 'N/A'; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>