<?php
require_once "conn.php";

if(isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    
    $sql = "SELECT * FROM WaterIntake WHERE WaterIntakeID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $water = $result->fetch_assoc();
    } else {
        die("Water intake record not found");
    }
}

if(isset($_POST['id']) && isset($_POST['amount']) && isset($_POST['time'])) {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $time = $_POST['time'];

    $sql = "UPDATE WaterIntake SET Amount = ?, Time = ? WHERE WaterIntakeID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("isi", $amount, $time, $id);
    
    if($stmt->execute()) {
        header("Location: water_tracking.php");
        exit();
    } else {
        die("Error updating water intake: " . $stmt->error);
    }
} else {
    die("Required fields are missing!");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Water Intake</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Water Intake</h2>
        <form method="POST">
            <input type="hidden" name="WaterIntakeID" value="<?php echo $water['WaterIntakeID']; ?>">
            
            <div class="mb-3">
                <label for="Amount" class="form-label">Amount (ml)</label>
                <input type="number" name="Amount" class="form-control" value="<?php echo htmlspecialchars($water['Amount']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Date" class="form-label">Date</label>
                <input type="date" name="Date" class="form-control" value="<?php echo htmlspecialchars($water['Date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Time" class="form-label">Time</label>
                <input type="time" name="Time" class="form-control" value="<?php echo htmlspecialchars($water['Time']); ?>" required>
            </div>

            <button type="submit" name="update" class="btn btn-primary">Update Water Intake</button>
            <a href="diet.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 