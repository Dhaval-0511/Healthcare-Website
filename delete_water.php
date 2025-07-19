<?php
require_once "conn.php";

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM WaterIntake WHERE WaterIntakeID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        header("Location: water_tracking.php");
        exit();
    } else {
        die("Error deleting water intake: " . $stmt->error);
    }
} else {
    die("No ID provided!");
}

$conn->close();
?> 