<?php
require_once "conn.php";
require_once "nutrition_api.php";

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get the date of the food item before deleting it
    $sql = "SELECT Date FROM FoodItems WHERE FoodItemID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $date = $result->fetch_assoc()['Date'];
    
    // Delete the food item
    $sql = "DELETE FROM FoodItems WHERE FoodItemID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        // Update daily calorie summary
        updateCalorieSummary($conn, $date);
        
        header("Location: food_tracking.php");
        exit();
    } else {
        die("Error deleting food item: " . $stmt->error);
    }
} else {
    die("No ID provided!");
}

$conn->close();
?> 