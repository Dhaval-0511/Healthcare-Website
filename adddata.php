<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;

// Handle regular student data
if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $grade = $_POST['grade'];
    $marks = $_POST['marks'];
    
    if($name != "" && $grade != "" && $marks != "") {
        $sql = "INSERT INTO results (name, class, marks) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssi", $name, $grade, $marks);
            if ($stmt->execute()) {
                header("location: index.php");
                exit();
            }
        }
        echo "Something went wrong. Please try again later.";
    } else {
        echo "Name, Class and Marks cannot be empty!";
    }
}

// Handle water intake submission
if(isset($_POST['add_water'])) {
    $amount = $_POST['amount'];
    $date = date('Y-m-d');
    
    if($amount > 0) {
        // First, check if there's already an entry for today
        $sql = "SELECT * FROM Water_Intake WHERE UserID = ? AND Date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            // Update existing entry
            $sql = "UPDATE Water_Intake SET Amount = Amount + ? WHERE UserID = ? AND Date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dis", $amount, $userId, $date);
        } else {
            // Insert new entry
            $sql = "INSERT INTO Water_Intake (UserID, Date, Amount) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isd", $userId, $date, $amount);
        }
        
        if($stmt->execute()) {
            header("location: water_tracking.php");
            exit();
        }
        echo "Error adding water intake.";
    }
}

// Handle food tracking submission
if(isset($_POST['add_food'])) {
    $foodName = $_POST['foodName'];
    $quantity = $_POST['quantity'];
    $mealType = $_POST['mealType'];
    $date = date('Y-m-d');
    
    // Insert into MealLogs table
    $sql = "INSERT INTO MealLogs (UserID, Date, MealType, FoodID, Quantity) 
            SELECT ?, ?, ?, FoodID, ? 
            FROM Food 
            WHERE FoodName = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issds", $userId, $date, $mealType, $quantity, $foodName);
        if ($stmt->execute()) {
            // Update Calories table
            $sql = "SELECT CaloriesPerServing FROM Food WHERE FoodName = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $foodName);
            $stmt->execute();
            $result = $stmt->get_result();
            $calories = $result->fetch_assoc()['CaloriesPerServing'] * $quantity;
            
            // Update or insert into Calories table
            $sql = "INSERT INTO Calories (UserID, Date, CaloriesGained) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE CaloriesGained = CaloriesGained + ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isdd", $userId, $date, $calories, $calories);
            $stmt->execute();
            
            // Update ratio and prediction
            updateHealthPrediction($userId, $date);
            
            header("location: food_tracking.php");
            exit();
        }
    }
    echo "Error adding food item.";
}

// Handle goal creation
if(isset($_POST['action']) && $_POST['action'] == 'add_goal') {
    if(isset($_POST['goalType']) && isset($_POST['goalValue']) && 
       isset($_POST['startDate']) && isset($_POST['endDate'])) {
        
        $goalType = $_POST['goalType'];
        $goalValue = $_POST['goalValue'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $status = 'Active';

        $sql = "INSERT INTO Goals (UserID, GoalType, GoalValue, StartDate, EndDate, Status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdsss", $userId, $goalType, $goalValue, $startDate, $endDate, $status);
        
        if($stmt->execute()) {
            header("location: goal_tracking.php");
            exit();
        } else {
            die("Error creating goal: " . $conn->error);
        }
    } else {
        die("All fields are required for creating a goal.");
    }
}

// Function to update health prediction
function updateHealthPrediction($userId, $date) {
    global $conn;
    
    // Get calories data
    $sql = "SELECT CaloriesBurned, CaloriesGained FROM Calories WHERE UserID = ? AND Date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if($data) {
        $burned = $data['CaloriesBurned'] ?? 0;
        $gained = $data['CaloriesGained'] ?? 0;
        $ratio = $burned / ($gained ?: 1);
        
        // Generate prediction
        $prediction = [];
        if($ratio < 0.5) {
            $prediction[] = "Risk of weight gain";
            if($gained > 3000) {
                $prediction[] = "High BP risk";
            }
        } elseif($ratio > 2) {
            $prediction[] = "Excessive calorie deficit";
            $prediction[] = "Low BP risk";
        }
        
        if($gained < 1200) {
            $prediction[] = "Low blood sugar risk";
        } elseif($gained > 4000) {
            $prediction[] = "Diabetes risk";
        }
        
        $predictionText = empty($prediction) ? "Normal health status" : implode(", ", $prediction);
        
        // Update Calories table
        $sql = "UPDATE Calories SET Ratio = ?, Prediction = ? WHERE UserID = ? AND Date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssi", $ratio, $predictionText, $userId, $date);
        $stmt->execute();
    }
}
?> 