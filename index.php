<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Tracker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .health-alert {
            border-left: 4px solid;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .alert-danger {
            border-left-color: #dc3545;
        }
        .alert-warning {
            border-left-color: #ffc107;
        }
        .alert-success {
            border-left-color: #198754;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .progress {
            height: 20px;
            border-radius: 10px;
        }
        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        .dark-mode-toggle:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        [data-theme="dark"] .dark-mode-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <?php
    if (isset($_SESSION['welcome_message']) && $_SESSION['welcome_message']) {
        echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <h4 class="alert-heading">Welcome, ' . htmlspecialchars($_SESSION['username']) . '!</h4>
                <p>Here\'s your health overview for today</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['welcome_message']);
    }
    ?>
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="mb-4">Dashboard</h2>
        <!-- Health Status Overview -->
        <?php
        // Get today's calorie data
        $sql = "SELECT CaloriesBurned, CaloriesGained, Ratio, Prediction 
                FROM Calories 
                WHERE UserID = ? AND Date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $calorieData = $result->fetch_assoc();

        $caloriesBurned = $calorieData['CaloriesBurned'] ?? 0;
        $caloriesGained = $calorieData['CaloriesGained'] ?? 0;
        $ratio = $calorieData['Ratio'] ?? 0;
        $prediction = $calorieData['Prediction'] ?? 'No data available';

        // Determine health status
        $healthStatus = 'success';
        $healthMessage = 'Your health metrics are within normal range.';
        
        if ($ratio < 0.5) {
            $healthStatus = 'danger';
            $healthMessage = 'Warning: Your calorie burn ratio is too low!';
        } elseif ($ratio > 2) {
            $healthStatus = 'warning';
            $healthMessage = 'Caution: Your calorie burn ratio is very high!';
        }
        ?>

        <div class="alert alert-<?php echo $healthStatus; ?> mb-4">
            <h4 class="alert-heading">
                <i class="fas fa-heartbeat"></i> 
                Daily Health Status
            </h4>
            <p><?php echo $healthMessage; ?></p>
            <hr>
            <p class="mb-0"><?php echo $prediction; ?></p>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-fire text-danger"></i>
                    <h5>Calories Burned</h5>
                    <div class="stat-value"><?php echo number_format($caloriesBurned); ?></div>
                    <small class="text-muted">Today's total</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-utensils text-success"></i>
                    <h5>Calories Gained</h5>
                    <div class="stat-value"><?php echo number_format($caloriesGained); ?></div>
                    <small class="text-muted">Today's intake</small>
                </div>
            </div>
            <div class="col-md-3">
                <?php
                // Get water intake
                $sql = "SELECT Amount FROM Water_Intake WHERE UserID = ? AND Date = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $userId, $today);
                $stmt->execute();
                $result = $stmt->get_result();
                $waterData = $result->fetch_assoc();
                $waterAmount = $waterData['Amount'] ?? 0;
                $waterGoal = 2.5; // 2.5 liters per day
                $waterProgress = ($waterAmount / $waterGoal) * 100;
                ?>
                <div class="stat-card">
                    <i class="fas fa-tint text-primary"></i>
                    <h5>Water Intake</h5>
                    <div class="stat-value"><?php echo number_format($waterAmount, 1); ?>L</div>
                    <div class="progress mt-2">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?php echo min(100, $waterProgress); ?>%">
                            <?php echo round($waterProgress); ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <?php
                // Get active goals count
                $sql = "SELECT COUNT(*) as active_goals FROM Goals WHERE UserID = ? AND Status = 'Active'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $goalData = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <i class="fas fa-bullseye text-info"></i>
                    <h5>Active Goals</h5>
                    <div class="stat-value"><?php echo $goalData['active_goals']; ?></div>
                    <small class="text-muted">In progress</small>
                </div>
            </div>
        </div>

        <!-- Detailed Sections -->
        <div class="row">
            <!-- Activity Summary -->
            <div class="col-md-6 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-running"></i> Today's Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT ActivityType, Duration, CaloriesBurned 
                                FROM Activities 
                                WHERE UserID = ? AND Date = ?
                                ORDER BY CreatedAt DESC
                                LIMIT 5";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $userId, $today);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0):
                        ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Duration</th>
                                        <th>Calories</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['ActivityType']; ?></td>
                                        <td><?php echo $row['Duration']; ?> min</td>
                                        <td><?php echo $row['CaloriesBurned']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-muted">No activities recorded today</p>
                        <?php endif; ?>
                        <a href="activity.php" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> Add Activity
                        </a>
                    </div>
                </div>
            </div>

            <!-- Meal Summary -->
            <div class="col-md-6 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-utensils"></i> Today's Meals
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT m.MealType, f.FoodName, m.Quantity, 
                                      (m.Quantity * f.CaloriesPerServing) as TotalCalories
                                FROM MealLogs m
                                JOIN Food f ON m.FoodID = f.FoodID
                                WHERE m.UserID = ? AND m.Date = ?
                                ORDER BY m.CreatedAt DESC
                                LIMIT 5";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $userId, $today);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0):
                        ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Meal</th>
                                        <th>Food</th>
                                        <th>Quantity</th>
                                        <th>Calories</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['MealType']; ?></td>
                                        <td><?php echo $row['FoodName']; ?></td>
                                        <td><?php echo $row['Quantity']; ?></td>
                                        <td><?php echo $row['TotalCalories']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-muted">No meals recorded today</p>
                        <?php endif; ?>
                        <a href="food_tracking.php" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> Add Meal
                        </a>
                    </div>
                </div>
            </div>

            <!-- Goals Progress -->
            <div class="col-md-12 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bullseye"></i> Active Goals Progress
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT * FROM Goals 
                                WHERE UserID = ? AND Status = 'Active' 
                                ORDER BY EndDate ASC
                                LIMIT 3";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                $today = new DateTime();
                                $endDate = new DateTime($row['EndDate']);
                                $startDate = new DateTime($row['StartDate']);
                                $totalDays = $startDate->diff($endDate)->days;
                                $remainingDays = $today->diff($endDate)->days;
                                $progress = $totalDays > 0 ? (($totalDays - $remainingDays) / $totalDays) * 100 : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><?php echo $row['GoalType']; ?> (Target: <?php echo $row['GoalValue']; ?>)</span>
                                <span class="text-muted"><?php echo $remainingDays; ?> days left</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo min(100, $progress); ?>%">
                                    <?php echo round($progress); ?>%
                                </div>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <p class="text-muted">No active goals</p>
                        <?php endif; ?>
                        <a href="goal_tracking.php" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> Set New Goal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2"
        crossorigin="anonymous"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 