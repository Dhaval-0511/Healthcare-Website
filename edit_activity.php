<?php
require_once "conn.php";

// Get activity details for editing
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM Activities WHERE ActivityID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $activity = $result->fetch_assoc();
    } else {
        die("Activity not found");
    }
}

// Handle form submission
if(isset($_POST['update'])) {
    $id = $_POST['id'];
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
        $sql = "UPDATE Activities SET ActivityType=?, Duration=?, CaloriesBurned=?, Date=? WHERE ActivityID=?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("siisi", $activityType, $duration, $caloriesBurned, $date, $id);
        
        if($stmt->execute()) {
            header("Location: activity.php");
            exit();
        } else {
            die("Error updating activity: " . $stmt->error);
        }
    } else {
        die("Required fields are missing!");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Activity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Edit Activity</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $activity['ActivityID']; ?>">
            
            <div class="mb-3">
                <label for="activityType" class="form-label">Activity Type</label>
                <select name="activityType" class="form-control" required>
                    <option value="">Select Activity</option>
                    <option value="Gym" <?php echo ($activity['ActivityType'] == 'Gym') ? 'selected' : ''; ?>>Gym</option>
                    <option value="Cycling" <?php echo ($activity['ActivityType'] == 'Cycling') ? 'selected' : ''; ?>>Cycling</option>
                    <option value="Yoga" <?php echo ($activity['ActivityType'] == 'Yoga') ? 'selected' : ''; ?>>Yoga</option>
                    <option value="Suryanamaskar" <?php echo ($activity['ActivityType'] == 'Suryanamaskar') ? 'selected' : ''; ?>>Suryanamaskar</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="duration" class="form-label">Duration (minutes)</label>
                <input type="number" name="duration" class="form-control" 
                       value="<?php echo $activity['Duration']; ?>" required min="1">
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" class="form-control" 
                       value="<?php echo $activity['Date']; ?>" required>
            </div>

            <button type="submit" name="update" class="btn btn-primary">Update Activity</button>
            <a href="activity.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 