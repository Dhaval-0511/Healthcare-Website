<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;

// Handle regular student data update
if(isset($_POST["name"]) && isset($_POST["grade"]) && isset($_POST["marks"])) {
    $name = $_POST['name'];
    $grade = $_POST['grade'];
    $marks = $_POST['marks'];
    $sql = "UPDATE results SET `name`= '$name', `class`= '$grade', `marks`= $marks  WHERE id= ".$_GET["id"];
    if (mysqli_query($conn, $sql)) {
        header("location: index.php");
    } else {
        echo "Something went wrong. Please try again later.";
    }
}

// Handle water intake update
if(isset($_POST['update_water'])) {
    $amount = $_POST['amount'];
    $userId = $_POST['user_id'];
    $date = $_POST['date'];
    
    if($amount > 0) {
        $sql = "UPDATE Water_Intake SET Amount = ? WHERE UserID = ? AND Date = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("dis", $amount, $userId, $date);
            if ($stmt->execute()) {
                header("location: water_tracking.php");
                exit();
            }
        }
        echo "Error updating water intake.";
    }
}

// Handle food update
if(isset($_POST['update_food'])) {
    $mealLogId = $_POST['meal_log_id'];
    $quantity = $_POST['quantity'];
    $date = $_POST['date'];
    
    // First get the old quantity to calculate calorie difference
    $sql = "SELECT m.Quantity as old_quantity, f.CaloriesPerServing 
            FROM MealLogs m 
            JOIN Food f ON m.FoodID = f.FoodID 
            WHERE m.UserID = ? AND m.Date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $oldCalories = $row['old_quantity'] * $row['CaloriesPerServing'];
    $newCalories = $quantity * $row['CaloriesPerServing'];
    $calorieDiff = $newCalories - $oldCalories;
    
    // Update MealLogs
    $sql = "UPDATE MealLogs SET Quantity = ? WHERE UserID = ? AND Date = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("dis", $quantity, $userId, $date);
        if ($stmt->execute()) {
            // Update Calories table
            $sql = "UPDATE Calories 
                    SET CaloriesGained = CaloriesGained + ? 
                    WHERE UserID = ? AND Date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dis", $calorieDiff, $userId, $date);
            $stmt->execute();
            
            // Update health prediction
            updateHealthPrediction($userId, $date);
            
            header("location: food_tracking.php");
            exit();
        }
    }
    echo "Error updating food item.";
}

// Handle goal updates
if(isset($_POST['action']) && $_POST['action'] == 'update_goal') {
    if(isset($_POST['original_goal_type']) && isset($_POST['original_start_date']) && 
       isset($_POST['goalValue']) && isset($_POST['endDate']) && isset($_POST['status'])) {
        
        $originalGoalType = $_POST['original_goal_type'];
        $originalStartDate = $_POST['original_start_date'];
        $goalValue = $_POST['goalValue'];
        $endDate = $_POST['endDate'];
        $status = $_POST['status'];

        $sql = "UPDATE Goals 
                SET GoalValue = ?, EndDate = ?, Status = ?
                WHERE UserID = ? AND GoalType = ? AND StartDate = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssiss", $goalValue, $endDate, $status, $userId, $originalGoalType, $originalStartDate);
        
        if($stmt->execute()) {
            header("location: goal_tracking.php");
            exit();
        } else {
            die("Error updating goal: " . $conn->error);
        }
    }
}

// Handle profile updates
if(isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $age = $_POST['age'] ? $_POST['age'] : null;
    $weight = $_POST['weight'] ? $_POST['weight'] : null;

    $sql = "UPDATE Users 
            SET Username = ?, Email = ?, Name = ?, Age = ?, Weight = ?
            WHERE UserID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssidi", $username, $email, $name, $age, $weight, $userId);
    
    if($stmt->execute()) {
        header("location: settings.php?success=profile");
        exit();
    } else {
        die("Error updating profile: " . $conn->error);
    }
}

// Handle health information updates
if(isset($_POST['action']) && $_POST['action'] == 'update_health') {
    $height = $_POST['height'] ? $_POST['height'] : null;
    $bloodGroup = $_POST['bloodGroup'];
    $diseases = $_POST['diseases'];

    // Check if record exists
    $sql = "SELECT UserID FROM Health_Details WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $sql = "UPDATE Health_Details 
                SET Height = ?, BloodGroup = ?, Diseases = ?
                WHERE UserID = ?";
    } else {
        $sql = "INSERT INTO Health_Details (Height, BloodGroup, Diseases, UserID) 
                VALUES (?, ?, ?, ?)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssi", $height, $bloodGroup, $diseases, $userId);
    
    if($stmt->execute()) {
        header("location: settings.php?success=health");
        exit();
    } else {
        die("Error updating health information: " . $conn->error);
    }
}

// Handle emergency contact updates
if(isset($_POST['action']) && $_POST['action'] == 'update_emergency') {
    $emergencyName = $_POST['emergencyName'];
    $emergencyPhone = $_POST['emergencyPhone'];
    $doctorName = $_POST['doctorName'];
    $doctorPhone = $_POST['doctorPhone'];

    // Check if record exists
    $sql = "SELECT UserID FROM EmergencyInfo WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $sql = "UPDATE EmergencyInfo 
                SET EmergencyContactName = ?, EmergencyContactPhone = ?, 
                    DoctorName = ?, DoctorPhone = ?
                WHERE UserID = ?";
    } else {
        $sql = "INSERT INTO EmergencyInfo 
                (EmergencyContactName, EmergencyContactPhone, DoctorName, DoctorPhone, UserID) 
                VALUES (?, ?, ?, ?, ?)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $emergencyName, $emergencyPhone, $doctorName, $doctorPhone, $userId);
    
    if($stmt->execute()) {
        header("location: settings.php?success=emergency");
        exit();
    } else {
        die("Error updating emergency information: " . $conn->error);
    }
}

// Handle app settings updates
if(isset($_POST['action']) && $_POST['action'] == 'update_settings') {
    $theme = $_POST['theme'];
    $notifications = isset($_POST['notifications']) ? 1 : 0;

    // Check if record exists
    $sql = "SELECT UserID FROM Settings WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $sql = "UPDATE Settings 
                SET ThemePreference = ?, NotificationEnabled = ?
                WHERE UserID = ?";
    } else {
        $sql = "INSERT INTO Settings (ThemePreference, NotificationEnabled, UserID) 
                VALUES (?, ?, ?)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $theme, $notifications, $userId);
    
    if($stmt->execute()) {
        header("location: settings.php?success=settings");
        exit();
    } else {
        die("Error updating app settings: " . $conn->error);
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