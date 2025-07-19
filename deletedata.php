<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;

// Handle regular student data deletion
if(isset($_GET["type"]) && $_GET["type"] == "student" && isset($_GET["id"])) {
    $sql = "DELETE FROM results WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if($stmt) {
        $stmt->bind_param("i", $_GET["id"]);
        if($stmt->execute()) {
            header("location: index.php");
            exit();
        }
    }
    echo "Error deleting student record.";
}

// Handle water intake deletion
if(isset($_GET["type"]) && $_GET["type"] == "water" && isset($_GET["id"])) {
    $sql = "DELETE FROM Water_Intake WHERE WaterIntakeID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    if($stmt) {
        $stmt->bind_param("ii", $_GET["id"], $userId);
        if($stmt->execute()) {
            header("location: water_tracking.php");
            exit();
        }
    }
    echo "Error deleting water intake record.";
}

// Handle food log deletion
if(isset($_GET["type"]) && $_GET["type"] == "food" && isset($_GET["id"])) {
    // First get the calories to be deducted
    $sql = "SELECT m.Quantity * f.CaloriesPerServing as calories_to_deduct, m.Date 
            FROM MealLogs m 
            JOIN Food f ON m.FoodID = f.FoodID 
            WHERE m.MealLogID = ? AND m.UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_GET["id"], $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if($row) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete from MealLogs
            $sql = "DELETE FROM MealLogs WHERE MealLogID = ? AND UserID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $_GET["id"], $userId);
            $stmt->execute();
            
            // Update Calories table
            $sql = "UPDATE Calories 
                    SET CaloriesGained = CaloriesGained - ? 
                    WHERE UserID = ? AND Date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dis", $row['calories_to_deduct'], $userId, $row['Date']);
            $stmt->execute();
            
            // Update health prediction
            updateHealthPrediction($userId, $row['Date']);
            
            // Commit transaction
            $conn->commit();
            
            header("location: food_tracking.php");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "Error deleting food log: " . $e->getMessage();
        }
    } else {
        echo "Food log not found.";
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