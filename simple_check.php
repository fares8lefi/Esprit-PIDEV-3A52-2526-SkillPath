<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=skillpathdb", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    foreach (['module', 'user', 'cours'] as $table) {
        echo "Table: $table\n";
        try {
            $q = $pdo->query("DESCRIBE $table");
            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        } catch (Exception $e) {
            echo "  - Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
