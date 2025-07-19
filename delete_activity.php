<?php
require_once "conn.php";

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Prepare delete statement
    $sql = "DELETE FROM Activities WHERE ActivityID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        header("Location: activity.php");
        exit();
    } else {
        die("Error deleting activity: " . $stmt->error);
    }
} else {
    die("No activity ID provided");
}

$conn->close();
?> 