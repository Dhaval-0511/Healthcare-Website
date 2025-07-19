<?php
require_once "conn.php";

if(isset($_POST['submit'])) {
    $activityType = $_POST['activityType'];
    $duration = $_POST['duration'];
    $date = $_POST['date'];

    // Calculate calories based on activity type and duration
    $caloriesPerMinute = [
        'Gym' => 7,
        'Cycling' => 8,
        'Yoga' => 4,
        'Suryanamaskar' => 3.8
    ];

    $caloriesBurned = round($caloriesPerMinute[$activityType] * $duration);

    if($activityType != "" && $duration != "" && $date != "") {
        $sql = "INSERT INTO Activities (ActivityType, Duration, CaloriesBurned, Date) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("siis", $activityType, $duration, $caloriesBurned, $date);
        
        if($stmt->execute()) {
            header("Location: activity.php");
            exit();
        } else {
            die("Error adding activity: " . $stmt->error);
        }
    } else {
        die("Required fields are missing!");
    }
}

$conn->close();
?> 