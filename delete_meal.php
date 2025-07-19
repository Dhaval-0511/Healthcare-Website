<?php
require_once "conn.php";

if(isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    
    $sql = "DELETE FROM Meals WHERE MealID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        header("Location: diet.php");
        exit();
    } else {
        die("Error deleting meal: " . $stmt->error);
    }
} else {
    die("No meal ID provided");
}

$conn->close();
?> 