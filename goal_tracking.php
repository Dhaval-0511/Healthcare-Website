<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goals - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        .goal-card {
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .goal-card:hover {
            transform: translateY(-5px);
        }
        .progress {
            height: 20px;
            border-radius: 10px;
        }
        .status-active {
            color: #198754;
        }
        .status-completed {
            color: #0d6efd;
        }
        .status-expired {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <!-- Main Content -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Goal Tracking</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                    <i class="fas fa-plus"></i> Set New Goal
                </button>
            </div>
            <div class="card-body">
                <!-- Goals Summary -->
                <?php
                $sql = "SELECT 
                            COUNT(*) as total_goals,
                            SUM(CASE WHEN Status = 'Active' THEN 1 ELSE 0 END) as active_goals,
                            SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) as completed_goals
                        FROM Goals 
                        WHERE UserID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $summary = $result->fetch_assoc();
                ?>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Total Goals</h5>
                                <p class="h2"><?php echo $summary['total_goals']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Active Goals</h5>
                                <p class="h2 text-success"><?php echo $summary['active_goals']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Completed Goals</h5>
                                <p class="h2 text-primary"><?php echo $summary['completed_goals']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Goals List -->
                <?php
                $sql = "SELECT * FROM Goals WHERE UserID = ? ORDER BY Status = 'Active' DESC, StartDate DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0):
                ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Goal Type</th>
                                <th>Target Value</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                $today = new DateTime();
                                $endDate = new DateTime($row['EndDate']);
                                $startDate = new DateTime($row['StartDate']);
                                $totalDays = $startDate->diff($endDate)->days;
                                $remainingDays = $today->diff($endDate)->days;
                                $progress = $totalDays > 0 ? (($totalDays - $remainingDays) / $totalDays) * 100 : 0;
                                
                                $statusClass = '';
                                switch($row['Status']) {
                                    case 'Active':
                                        $statusClass = 'status-active';
                                        break;
                                    case 'Completed':
                                        $statusClass = 'status-completed';
                                        break;
                                    default:
                                        $statusClass = 'status-expired';
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['GoalType']); ?></td>
                                    <td><?php echo htmlspecialchars($row['GoalValue']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($row['StartDate'])); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($row['EndDate'])); ?></td>
                                    <td>
                                        <span class="<?php echo $statusClass; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo $row['Status']; ?>
                                        </span>
                                    </td>
                                    <td style="width: 20%;">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo min(100, $progress); ?>%"
                                                 aria-valuenow="<?php echo $progress; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php echo round($progress); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="editGoal('<?php echo $row['GoalType']; ?>', 
                                                                 '<?php echo $row['StartDate']; ?>', 
                                                                 '<?php echo $row['GoalValue']; ?>',
                                                                 '<?php echo $row['EndDate']; ?>',
                                                                 '<?php echo $row['Status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="deletedata.php?type=goal&goaltype=<?php echo urlencode($row['GoalType']); ?>&startdate=<?php echo urlencode($row['StartDate']); ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this goal?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No goals set yet. Click "Set New Goal" to get started!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Goal Modal -->
    <div class="modal fade" id="addGoalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Set New Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="adddata.php" method="post">
                        <input type="hidden" name="action" value="add_goal">
                        <div class="mb-3">
                            <label for="goalType" class="form-label">Goal Type</label>
                            <select name="goalType" id="goalType" class="form-select" required>
                                <option value="">Select Goal Type</option>
                                <option value="Weight Loss">Weight Loss</option>
                                <option value="Daily Steps">Daily Steps</option>
                                <option value="Water Intake">Water Intake</option>
                                <option value="Calorie Control">Calorie Control</option>
                                <option value="Exercise Minutes">Exercise Minutes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="goalValue" class="form-label">Target Value</label>
                            <input type="number" name="goalValue" id="goalValue" 
                                   class="form-control" step="0.01" required>
                            <small class="form-text text-muted">
                                Enter target value (e.g., weight in kg, steps per day, etc.)
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" name="startDate" id="startDate" 
                                   class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" name="endDate" id="endDate" 
                                   class="form-control" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_goal" class="btn btn-primary">Set Goal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Goal Modal -->
    <div class="modal fade" id="editGoalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="updatedata.php" method="post">
                        <input type="hidden" name="action" value="update_goal">
                        <input type="hidden" name="original_goal_type" id="edit_original_goal_type">
                        <input type="hidden" name="original_start_date" id="edit_original_start_date">
                        
                        <div class="mb-3">
                            <label for="edit_goalType" class="form-label">Goal Type</label>
                            <input type="text" id="edit_goalType" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_goalValue" class="form-label">Target Value</label>
                            <input type="number" name="goalValue" id="edit_goalValue" 
                                   class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_startDate" class="form-label">Start Date</label>
                            <input type="date" id="edit_startDate" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_endDate" class="form-label">End Date</label>
                            <input type="date" name="endDate" id="edit_endDate" 
                                   class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_goal" class="btn btn-primary">Update Goal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
    <script>
        function editGoal(goalType, startDate, goalValue, endDate, status) {
            document.getElementById('edit_original_goal_type').value = goalType;
            document.getElementById('edit_original_start_date').value = startDate;
            document.getElementById('edit_goalType').value = goalType;
            document.getElementById('edit_goalValue').value = goalValue;
            document.getElementById('edit_startDate').value = startDate;
            document.getElementById('edit_endDate').value = endDate;
            document.getElementById('edit_status').value = status;
            new bootstrap.Modal(document.getElementById('editGoalModal')).show();
        }

        // Validate date ranges
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            
            startDate.min = new Date().toISOString().split('T')[0];
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
            });
        });
    </script>
</body>
</html> 