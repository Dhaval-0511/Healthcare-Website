<?php
require_once "CRUD/conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;
$today = date('Y-m-d');

// Handle form submission
if(isset($_POST['add_water'])) {
    $amount = $_POST['amount'];
    
    // First check if there's an entry for today
    $check_sql = "SELECT Amount FROM Water_Intake WHERE UserID = ? AND Date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $userId, $today);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($row = $check_result->fetch_assoc()) {
        // Update existing record
        $new_amount = $row['Amount'] + $amount;
        $sql = "UPDATE Water_Intake SET Amount = ? WHERE UserID = ? AND Date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dis", $new_amount, $userId, $today);
    } else {
        // Insert new record
        $sql = "INSERT INTO Water_Intake (UserID, Amount, Date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ids", $userId, $amount, $today);
    }
    
    if($stmt->execute()) {
        $success_message = "Water intake recorded successfully!";
    } else {
        $error_message = "Error recording water intake. Please try again.";
    }
    $check_stmt->close();
    $stmt->close();
}

// Get total water intake for today
$sql = "SELECT Amount as total FROM Water_Intake WHERE UserID = ? AND Date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userId, $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_intake = $row['total'] ?? 0;
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Tracking - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        /* Your existing styles */
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-tint me-2"></i>Water Tracking</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if(isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <h5>Today's Water Intake</h5>
                            <div class="display-4 text-primary mb-3"><?php echo $total_intake; ?> ml</div>
                            <div class="progress mb-3" style="height: 20px;">
                                <?php $percentage = min(($total_intake / 2500) * 100, 100); ?>
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                    <?php echo round($percentage); ?>%
                                </div>
                            </div>
                            <small class="text-muted">Daily Goal: 2500 ml</small>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Add Water Intake (ml)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="amount" name="amount" required min="0" step="50">
                                    <button type="submit" name="add_water" class="btn btn-primary">Add</button>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button class="btn btn-outline-primary" onclick="document.getElementById('amount').value='250'">+ 250ml</button>
                            <button class="btn btn-outline-primary" onclick="document.getElementById('amount').value='500'">+ 500ml</button>
                            <button class="btn btn-outline-primary" onclick="document.getElementById('amount').value='1000'">+ 1000ml</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 