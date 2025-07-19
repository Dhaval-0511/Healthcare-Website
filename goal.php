<?php
// Remove session check to allow direct access
require_once "conn.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .status-in-progress { background-color: #ffd700; color: #000; }
        .status-completed { background-color: #28a745; color: #fff; }
        .status-failed { background-color: #dc3545; color: #fff; }
    </style>
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

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Goal Management</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                    <i class="fas fa-plus"></i> Add New Goal
                </button>
            </div>

            <!-- Add Goal Modal -->
            <div class="modal fade" id="addGoalModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Goal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="add_goal.php" method="post">
                                <div class="mb-3">
                                    <label for="GoalType" class="form-label">Goal Type</label>
                                    <select name="GoalType" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="Weight">Weight</option>
                                        <option value="Calories">Calories</option>
                                        <option value="Steps">Steps</option>
                                        <option value="Exercise">Exercise Minutes</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="GoalValue" class="form-label">Goal Value</label>
                                    <input type="number" name="GoalValue" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="StartDate" class="form-label">Start Date</label>
                                    <input type="date" name="StartDate" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="EndDate" class="form-label">End Date</label>
                                    <input type="date" name="EndDate" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="Status" class="form-label">Status</label>
                                    <select name="Status" class="form-control" required>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Failed">Failed</option>
                                    </select>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="submit" class="btn btn-primary">Add Goal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Goal Type</th>
                            <th>Goal Value</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sql_query = "SELECT * FROM Goals ORDER BY StartDate DESC";
                        $result = $conn->query($sql_query);

                        while ($row = $result->fetch_assoc()) { 
                            $status_class = '';
                            switch($row['Status']) {
                                case 'In Progress':
                                    $status_class = 'status-in-progress';
                                    break;
                                case 'Completed':
                                    $status_class = 'status-completed';
                                    break;
                                case 'Failed':
                                    $status_class = 'status-failed';
                                    break;
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['GoalType']); ?></td>
                            <td><?php echo htmlspecialchars($row['GoalValue']); ?></td>
                            <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                            <td><?php echo htmlspecialchars($row['EndDate']); ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                            <td>
                                <a href="edit_goal.php?id=<?php echo $row['GoalID']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_goal.php?id=<?php echo $row['GoalID']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this goal?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 