<?php
require_once "CRUD/conn.php";
session_start();

// For demo purposes, we'll use a fixed UserID (you should get this from session)
$userId = 1; // Replace this with actual session user ID in production

// Fetch user details from multiple tables
$userQuery = "SELECT u.*, h.Height, h.BloodGroup, h.Diseases, 
    e.EmergencyContactName, e.EmergencyContactPhone, e.DoctorName, e.DoctorPhone 
    FROM Users u 
    LEFT JOIN Health_Details h ON u.UserID = h.UserID 
    LEFT JOIN EmergencyInfo e ON u.UserID = e.UserID 
    WHERE u.UserID = ?";

$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-section {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-header {
            background-color: #e9ecef;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #6c757d;
            margin: 0 auto 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .main-content {
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($user['Name']); ?></h2>
                    <p class="text-muted">@<?php echo htmlspecialchars($user['Username']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container main-content mb-5">
        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
                <div class="profile-section">
                    <h3><i class="fas fa-user-circle me-2"></i>Personal Information</h3>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Name:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['Name']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Email:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['Email']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Age:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['Age']); ?> years</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Weight:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['Weight']); ?> kg</div>
                    </div>
                    <div class="text-end">
                        <a href="settings.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    </div>
                </div>

                <!-- Health Information -->
                <div class="profile-section">
                    <h3><i class="fas fa-heartbeat me-2"></i>Health Information</h3>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Height:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['Height']); ?> cm</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Blood Group:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['BloodGroup']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Medical Conditions:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['Diseases']); ?></div>
                    </div>
                    <div class="text-end">
                        <a href="settings.php#health" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="col-md-6">
                <div class="profile-section">
                    <h3><i class="fas fa-phone-alt me-2"></i>Emergency Contacts</h3>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Emergency Contact:</strong></div>
                        <div class="col-sm-8">
                            <?php echo htmlspecialchars($user['EmergencyContactName']); ?><br>
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>
                                <?php echo htmlspecialchars($user['EmergencyContactPhone']); ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Doctor:</strong></div>
                        <div class="col-sm-8">
                            <?php echo htmlspecialchars($user['DoctorName']); ?><br>
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>
                                <?php echo htmlspecialchars($user['DoctorPhone']); ?>
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="settings.php#emergency" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    </div>
                </div>

                <!-- Activity Summary -->
                <div class="profile-section">
                    <h3><i class="fas fa-chart-line me-2"></i>Activity Summary</h3>
                    <hr>
                    <?php
                    // Fetch recent activity summary
                    $activityQuery = "SELECT 
                        COUNT(DISTINCT Date) as active_days,
                        SUM(CaloriesBurned) as total_calories,
                        SUM(Duration) as total_duration
                        FROM Activities 
                        WHERE UserID = ? 
                        AND Date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
                    
                    $stmt = $conn->prepare($activityQuery);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $activityResult = $stmt->get_result();
                    $activity = $activityResult->fetch_assoc();
                    ?>
                    <div class="row text-center">
                        <div class="col-4">
                            <h4><?php echo $activity['active_days']; ?></h4>
                            <small class="text-muted">Active Days</small>
                        </div>
                        <div class="col-4">
                            <h4><?php echo number_format($activity['total_calories']); ?></h4>
                            <small class="text-muted">Calories Burned</small>
                        </div>
                        <div class="col-4">
                            <h4><?php echo floor($activity['total_duration'] / 60); ?></h4>
                            <small class="text-muted">Hours Active</small>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <a href="activity.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-history me-1"></i>View History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 