<?php
require_once 'config/database.php';
include 'includes/header.php';

// Fetch tank inventory with fuel details
$inventory = $pdo->query("
    SELECT t.*, f.fuel_name, f.price_per_liter,
           (t.current_stock / t.capacity * 100) as stock_percentage
    FROM tank t
    JOIN fuel_type f ON t.fuel_id = f.fuel_id
")->fetchAll();

// Fetch recent deliveries
$deliveries = $pdo->query("
    SELECT d.*, s.supplier_name, f.fuel_name
    FROM fuel_delivery d
    JOIN supplier s ON d.supplier_id = s.supplier_id
    JOIN tank t ON d.tank_id = t.tank_id
    JOIN fuel_type f ON t.fuel_id = f.fuel_id
    ORDER BY d.delivery_date DESC
    LIMIT 10
")->fetchAll();
?>

<h2>Inventory Management</h2>

<h3>Current Tank Levels</h3>
<table>
    <thead>
        <tr>
            <th>Tank ID</th>
            <th>Fuel Type</th>
            <th>Capacity (L)</th>
            <th>Current Stock (L)</th>
            <th>Stock Level</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($inventory as $tank): ?>
        <tr>
            <td><?php echo $tank['tank_id']; ?></td>
            <td><?php echo $tank['fuel_name']; ?></td>
            <td><?php echo number_format($tank['capacity'], 0); ?></td>
            <td><?php echo number_format($tank['current_stock'], 0); ?></td>
            <td>
                <div style="background-color: #f0f0f0; width: 100%; height: 20px; border-radius: 10px;">
                    <div style="background-color: <?php echo $tank['stock_percentage'] > 30 ? '#28a745' : '#dc3545'; ?>; width: <?php echo $tank['stock_percentage']; ?>%; height: 20px; border-radius: 10px;"></div>
                </div>
            </td>
            <td>
                <?php if ($tank['stock_percentage'] <= 20): ?>
                    <span style="color: #dc3545; font-weight: bold;">Critical - Order Now</span>
                <?php elseif ($tank['stock_percentage'] <= 50): ?>
                    <span style="color: #ffc107; font-weight: bold;">Low Stock</span>
                <?php else: ?>
                    <span style="color: #28a745; font-weight: bold;">Good</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="margin-top: 30px;">Recent Fuel Deliveries</h3>
<table>
    <thead>
        <tr>
            <th>Delivery ID</th>
            <th>Supplier</th>
            <th>Fuel Type</th>
            <th>Quantity (L)</th>
            <th>Delivery Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($deliveries as $delivery): ?>
        <tr>
            <td><?php echo $delivery['delivery_id']; ?></td>
            <td><?php echo $delivery['supplier_name']; ?></td>
            <td><?php echo $delivery['fuel_name']; ?></td>
            <td><?php echo number_format($delivery['quantity'], 0); ?></td>
            <td><?php echo $delivery['delivery_date']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>