<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'healthcare_db');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("Error setting charset: " . mysqli_error($conn));
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Function to close database connection
function close_connection() {
    global $conn;
    if(isset($conn)) {
        mysqli_close($conn);
    }
}

// Register shutdown function to close connection
register_shutdown_function('close_connection');
?> 