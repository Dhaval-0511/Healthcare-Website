<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        /* Your existing styles */
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="mb-4">Activities</h2>
        <section>
            <h1 style="text-align: center;margin: 50px 0;">Activity Management</h1>
            <div class="container">
                <form action="add_activity.php" method="post">
                    <div class="row">
                        <div class="form-group col-lg-3">
                            <label for="activityType">Activity Type</label>
                            <select name="activityType" id="activityType" class="form-control" required>
                                <option value="">Select Activity</option>
                                <option value="Gym">Gym</option>
                                <option value="Cycling">Cycling</option>
                                <option value="Yoga">Yoga</option>
                                <option value="Suryanamaskar">Suryanamaskar</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="duration">Duration (minutes)</label>
                            <input type="number" name="duration" id="duration" class="form-control" required min="1">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group col-lg-3" style="display: grid;align-items: flex-end;">
                            <input type="submit" name="submit" id="submit" class="btn btn-primary" value="Add Activity">
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section style="margin: 50px 0;">
            <div class="container">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Today's Activities</h5>
                                <?php
                                require_once "conn.php";
                                $today = date('Y-m-d');
                                $sql = "SELECT COUNT(*) as count, SUM(CaloriesBurned) as calories 
                                       FROM Activities WHERE Date = '$today'";
                                $result = $conn->query($sql);
                                $row = $result->fetch_assoc();
                                ?>
                                <h3><?php echo $row['count'] ?? 0; ?></h3>
                                <p>Total Calories: <?php echo $row['calories'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Weekly Stats</h5>
                                <?php
                                $week_start = date('Y-m-d', strtotime('-7 days'));
                                $sql = "SELECT COUNT(*) as count, SUM(Duration) as duration 
                                       FROM Activities WHERE Date >= '$week_start'";
                                $result = $conn->query($sql);
                                $row = $result->fetch_assoc();
                                ?>
                                <h3><?php echo $row['count'] ?? 0; ?> Activities</h3>
                                <p>Total Duration: <?php echo $row['duration'] ?? 0; ?> mins</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Calories Burned</h5>
                                <?php
                                $sql = "SELECT SUM(CaloriesBurned) as total FROM Activities";
                                $result = $conn->query($sql);
                                $row = $result->fetch_assoc();
                                ?>
                                <h3><?php echo $row['total'] ?? 0; ?></h3>
                                <p>All Time</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activities Table -->
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Activity Type</th>
                            <th>Duration (mins)</th>
                            <th>Calories Burned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sql_query = "SELECT * FROM Activities ORDER BY Date DESC, ActivityID DESC";
                        if ($result = $conn->query($sql_query)) {
                            while ($row = $result->fetch_assoc()) { 
                                $id = $row['ActivityID'];
                                $activityType = $row['ActivityType'];
                                $duration = $row['Duration'];
                                $calories = $row['CaloriesBurned'];
                                $date = $row['Date'];
                        ?>
                        <tr>
                            <td><?php echo $date; ?></td>
                            <td><?php echo $activityType; ?></td>
                            <td><?php echo $duration; ?></td>
                            <td><?php echo $calories; ?></td>
                            <td>
                                <a href="edit_activity.php?id=<?php echo $id; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_activity.php?id=<?php echo $id; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this activity?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                            } 
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 