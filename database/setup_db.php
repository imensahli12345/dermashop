<?php
/**
 * DermaShop Database Setup Script
 * 
 * This script creates the necessary database tables for the DermaShop e-commerce website.
 * Run this script once to set up your database.
 */

// Include the database configuration file
require_once '../includes/config.php';

echo "<h1>DermaShop Database Setup</h1>";
echo "<p>Setting up database tables...</p>";

try {
    // Read the SQL file
    $sql = file_get_contents('dermashop_schema.sql');
    
    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)), 'strlen');
    
    // Execute each statement
    foreach ($statements as $statement) {
        $connexion->exec($statement);
        echo "<p>Executed: " . substr($statement, 0, 50) . "...</p>";
    }
    
    echo "<h2>Database setup completed successfully!</h2>";
    echo "<p>The following tables were created or already existed:</p>";
    echo "<ul>";
    
    // List the tables
    $tables = $connexion->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    
    echo "</ul>";
    echo "<p><a href='../index.php'>Return to homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Error setting up database</h2>";
    echo "<p>Error message: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and SQL file.</p>";
}
?> 