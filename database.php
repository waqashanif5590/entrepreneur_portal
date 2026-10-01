<?php
// database.php - Smart auto setup (Recommended for your project)

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_enterpreneur";

$conn = new mysqli($servername, $username, $password, $dbname);

// If connection fails (database doesn't exist or tables missing), run setup
if ($conn->connect_error) {
    // Connect without selecting database
    $conn = new mysqli($servername, $username, $password);

    // Run setup
    include 'setup.php';   // This will create DB + tables + admin and redirect

    // Reconnect after setup
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Database connection failed after setup: " . $conn->connect_error);
    }
}
// If we reach here, database and tables are ready
