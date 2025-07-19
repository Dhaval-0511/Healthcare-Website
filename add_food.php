<?php
require_once "conn.php";
require_once "nutrition_api.php";

if(isset($_POST['foodName']) && isset($_POST['quantity']) && isset($_POST['mealType'])) {
    $foodName = $_POST['foodName'];
    $quantity = $_POST['quantity'];
    $mealType = $_POST['mealType'];
    $date = date('Y-m-d');

    // Get nutritional information
    $nutrition = getNutritionalInfo($foodName, $quantity);
    
    $sql = "INSERT INTO FoodItems (FoodName, Quantity, MealType, Date, Protein, Fat, Calories) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ssssddd", 
        $foodName, 
        $quantity, 
        $mealType, 
        $date, 
        $nutrition['protein'],
        $nutrition['fat'],
        $nutrition['calories']
    );
    
    if($stmt->execute()) {
        // Update daily calorie summary
        updateCalorieSummary($conn, $date);
        header("Location: diet.php");
        exit();
    } else {
        die("Error adding food item: " . $stmt->error);
    }
} else {
    die("Required fields are missing!");
}

$conn->close();
?> 