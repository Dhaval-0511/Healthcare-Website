<?php
require_once "conn.php";

if(isset($_POST['submit'])) {
    $errors = array();
    
    // Validate and sanitize inputs
    $MealType = isset($_POST['MealType']) ? sanitize_input($_POST['MealType']) : '';
    $FoodItems = isset($_POST['FoodItems']) ? sanitize_input($_POST['FoodItems']) : '';
    $Calories = isset($_POST['Calories']) ? sanitize_input($_POST['Calories']) : '';
    $Protein = isset($_POST['Protein']) ? sanitize_input($_POST['Protein']) : 0;
    $Carbs = isset($_POST['Carbs']) ? sanitize_input($_POST['Carbs']) : 0;
    $Fat = isset($_POST['Fat']) ? sanitize_input($_POST['Fat']) : 0;
    $Date = isset($_POST['Date']) ? sanitize_input($_POST['Date']) : '';

    // Validation
    if(empty($MealType)) {
        $errors[] = "Meal type is required";
    }
    if(empty($FoodItems)) {
        $errors[] = "Food items are required";
    }
    if(empty($Calories) || !is_numeric($Calories) || $Calories < 0) {
        $errors[] = "Valid calories are required";
    }
    if(!empty($Protein) && (!is_numeric($Protein) || $Protein < 0)) {
        $errors[] = "Protein must be a positive number";
    }
    if(!empty($Carbs) && (!is_numeric($Carbs) || $Carbs < 0)) {
        $errors[] = "Carbs must be a positive number";
    }
    if(!empty($Fat) && (!is_numeric($Fat) || $Fat < 0)) {
        $errors[] = "Fat must be a positive number";
    }
    if(empty($Date) || !strtotime($Date)) {
        $errors[] = "Valid date is required";
    }

    // If no errors, proceed with insertion
    if(empty($errors)) {
        $sql = "INSERT INTO Meals (MealType, FoodItems, Calories, Protein, Carbs, Fat, Date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ssiddds", $MealType, $FoodItems, $Calories, $Protein, $Carbs, $Fat, $Date);
        
        if($stmt->execute()) {
            header("Location: diet.php?success=1");
            exit();
        } else {
            $errors[] = "Error adding meal: " . $stmt->error;
        }
    }

    // If there were errors, redirect back with error messages
    if(!empty($errors)) {
        $error_string = implode(", ", $errors);
        header("Location: diet.php?error=" . urlencode($error_string));
        exit();
    }
}

$conn->close();
?> 