<?php
require_once "conn.php";

if(isset($_POST['amount']) && isset($_POST['time'])) {
    $amount = $_POST['amount'];
    $time = $_POST['time'];
    $date = date('Y-m-d');

    $sql = "INSERT INTO WaterIntake (Amount, Date, Time) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("iss", $amount, $date, $time);
    
    if($stmt->execute()) {
        header("Location: diet.php");
        exit();
    } else {
        die("Error adding water intake: " . $stmt->error);
    }
} else {
    die("Required fields are missing!");
}

$conn->close();
?> 