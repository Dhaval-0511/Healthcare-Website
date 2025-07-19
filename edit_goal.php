<?php
require_once "conn.php";

// Check if goal ID is provided
if (!isset($_GET['id'])) {
    header("Location: goal.php");
    exit();
}

$GoalID = $_GET['id'];

// Get goal details
$sql = "SELECT * FROM Goals WHERE GoalID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $GoalID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Goal not found!");
}

$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $GoalType = $_POST['GoalType'];
    $GoalValue = $_POST['GoalValue'];
    $StartDate = $_POST['StartDate'];
    $EndDate = $_POST['EndDate'];
    $Status = $_POST['Status'];

    // Validate dates
    if (strtotime($EndDate) < strtotime($StartDate)) {
        $error = "End date cannot be earlier than start date";
    } else {
        $sql = "UPDATE Goals SET 
                GoalType = ?, 
                GoalValue = ?, 
                StartDate = ?, 
                EndDate = ?, 
                Status = ? 
                WHERE GoalID = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("sssssi", $GoalType, $GoalValue, $StartDate, $EndDate, $Status, $GoalID);

        if ($stmt->execute()) {
            header("Location: goal.php");
            exit();
        } else {
            $error = "Error updating goal: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Goal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Healthcare Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="goal.php">Goals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="activity.php">Activities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="diet.php">Diet</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Edit Goal</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="GoalType" class="form-label">Goal Type</label>
                                <select name="GoalType" id="GoalType" class="form-control" required>
                                    <option value="Weight" <?php echo ($row['GoalType'] == 'Weight') ? 'selected' : ''; ?>>Weight</option>
                                    <option value="Calories" <?php echo ($row['GoalType'] == 'Calories') ? 'selected' : ''; ?>>Calories</option>
                                    <option value="Steps" <?php echo ($row['GoalType'] == 'Steps') ? 'selected' : ''; ?>>Steps</option>
                                    <option value="Exercise" <?php echo ($row['GoalType'] == 'Exercise') ? 'selected' : ''; ?>>Exercise Minutes</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="GoalValue" class="form-label">Goal Value</label>
                                <input type="number" name="GoalValue" id="GoalValue" class="form-control" 
                                       value="<?php echo htmlspecialchars($row['GoalValue']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="StartDate" class="form-label">Start Date</label>
                                <input type="date" name="StartDate" id="StartDate" class="form-control" 
                                       value="<?php echo htmlspecialchars($row['StartDate']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="EndDate" class="form-label">End Date</label>
                                <input type="date" name="EndDate" id="EndDate" class="form-control" 
                                       value="<?php echo htmlspecialchars($row['EndDate']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="Status" class="form-label">Status</label>
                                <select name="Status" id="Status" class="form-control" required>
                                    <option value="In Progress" <?php echo ($row['Status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo ($row['Status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Failed" <?php echo ($row['Status'] == 'Failed') ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="goal.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Goal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add client-side date validation
        document.getElementById('EndDate').addEventListener('change', function() {
            var startDate = new Date(document.getElementById('StartDate').value);
            var endDate = new Date(this.value);
            
            if (endDate < startDate) {
                alert('End date cannot be earlier than start date');
                this.value = '';
            }
        });
    </script>
</body>
</html> 