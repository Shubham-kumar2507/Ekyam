<?php
require_once 'config.php';

// Read the SQL file
$sql = file_get_contents('add_community_events.sql');

// Execute each statement
$statements = explode(';', $sql);
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            echo "Error executing statement: " . $conn->error . "\n";
        }
    }
}

echo "Tables created successfully!\n";
?> 