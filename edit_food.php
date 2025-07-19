<?php
require_once "conn.php";
require_once "nutrition_api.php";

if(isset($_POST['id']) && isset($_POST['foodName']) && isset($_POST['quantity']) && isset($_POST['mealType'])) {
    $id = $_POST['id'];
    $foodName = $_POST['foodName'];
    $quantity = $_POST['quantity'];
    $mealType = $_POST['mealType'];
    
    // Get updated nutritional information
    $nutrition = getNutritionalInfo($foodName, $quantity);
    
    $sql = "UPDATE FoodItems 
            SET FoodName = ?, 
                Quantity = ?, 
                MealType = ?, 
                Protein = ?, 
                Fat = ?, 
                Calories = ? 
            WHERE FoodItemID = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("sssdddi", 
        $foodName, 
        $quantity, 
        $mealType, 
        $nutrition['protein'],
        $nutrition['fat'],
        $nutrition['calories'],
        $id
    );
    
    if($stmt->execute()) {
        // Get the date of the updated food item to update the calorie summary
        $sql = "SELECT Date FROM FoodItems WHERE FoodItemID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $date = $result->fetch_assoc()['Date'];
        
        // Update daily calorie summary
        updateCalorieSummary($conn, $date);
        
        header("Location: food_tracking.php");
        exit();
    } else {
        die("Error updating food item: " . $stmt->error);
    }
} else {
    die("Required fields are missing!");
}

$conn->close();
?> 