<?php
require_once "conn.php";

if(isset($_POST['submit'])){
    $GoalType = $_POST['GoalType'];
    $GoalValue = $_POST['GoalValue'];
    $StartDate = $_POST['StartDate'];
    $EndDate = $_POST['EndDate'];
    $Status = $_POST['Status'];

    // Validate dates
    if (strtotime($EndDate) < strtotime($StartDate)) {
        die("End date cannot be earlier than start date");
    }

    if($GoalType != "" && $GoalValue != "" && $StartDate != "" && $EndDate != "" && $Status != ""){
        $sql = "INSERT INTO Goals (GoalType, GoalValue, StartDate, EndDate, Status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("sssss", $GoalType, $GoalValue, $StartDate, $EndDate, $Status);
        
        if ($stmt->execute()) {
            header("Location: goal.php");
            exit();
        } else {
            die("Error adding goal: " . $stmt->error);
        }
    } else {
        die("All fields are required!");
    }
}

$conn->close();
?> 