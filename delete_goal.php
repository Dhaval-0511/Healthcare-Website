<?php
require_once "conn.php";

if (isset($_GET['id'])) {
    $goal_id = $_GET['id'];
    
    // First check if goal exists
    $check_sql = "SELECT id FROM goals WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $check_stmt->bind_param("i", $goal_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        die("Goal not found!");
    }
    
    // Delete the goal
    $sql = "DELETE FROM goals WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $goal_id);
    
    if ($stmt->execute()) {
        header("Location: goal.php");
        exit();
    } else {
        die("Error deleting goal: " . $stmt->error);
    }
} else {
    die("No goal specified for deletion.");
}

$conn->close();
?> 