<?php
echo "<h2>Database Connection Diagnostic</h2>";

// Check if MySQL is running
$connection = @mysqli_connect('localhost', 'Erneste304tech', 'Password123');
if (!$connection) {
    echo "<p style='color: red;'>❌ Cannot connect to MySQL server: " . mysqli_connect_error() . "</p>";
} else {
    echo "<p style='color: green;'>✓ MySQL server is running</p>";
    
    // Check if database exists
    if (mysqli_select_db($connection, 'petroleum_station_db')) {
        echo "<p style='color: green;'>✓ Database 'petroleum_station_db' exists</p>";
        
        // Check tables
        $result = mysqli_query($connection, "SHOW TABLES");
        echo "<p>Tables in database:</p><ul>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Database 'petroleum_station_db' does not exist</p>";
    }
    
    mysqli_close($connection);
}

// Check PHP PDO extension
echo "<p>PDO Extension: " . (class_exists('PDO') ? '✓ Installed' : '❌ Not installed') . "</p>";
?>