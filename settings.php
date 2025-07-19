<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;

// Fetch user data
$sql = "SELECT u.*, s.ThemePreference, s.NotificationEnabled, h.Height, h.BloodGroup, h.Diseases
        FROM Users u 
        LEFT JOIN Settings s ON u.UserID = s.UserID
        LEFT JOIN Health_Details h ON u.UserID = h.UserID
        WHERE u.UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Fetch emergency contact info
$sql = "SELECT * FROM EmergencyInfo WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$emergencyData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        .settings-card {
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .theme-preview {
            width: 100%;
            height: 80px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .theme-light {
            background: #ffffff;
            border: 1px solid #dee2e6;
        }
        .theme-dark {
            background: #343a40;
            border: 1px solid #495057;
        }
        .profile-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="mb-4">Settings</h2>
        <div class="row">
            <!-- Profile Settings -->
            <div class="col-md-6 mb-4">
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-circle"></i> Profile Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="updatedata.php" method="post">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($userData['Username'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($userData['Email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($userData['Name'] ?? ''); ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" name="age" 
                                           value="<?php echo htmlspecialchars($userData['Age'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" id="weight" name="weight" 
                                           value="<?php echo htmlspecialchars($userData['Weight'] ?? ''); ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Health Information -->
            <div class="col-md-6 mb-4">
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Health Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="updatedata.php" method="post">
                            <input type="hidden" name="action" value="update_health">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="height" class="form-label">Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control" id="height" name="height" 
                                           value="<?php echo htmlspecialchars($userData['Height'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bloodGroup" class="form-label">Blood Group</label>
                                    <select class="form-select" id="bloodGroup" name="bloodGroup">
                                        <option value="">Select Blood Group</option>
                                        <?php
                                        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($bloodGroups as $bg) {
                                            $selected = ($userData['BloodGroup'] == $bg) ? 'selected' : '';
                                            echo "<option value=\"$bg\" $selected>$bg</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="diseases" class="form-label">Medical Conditions</label>
                                <textarea class="form-control" id="diseases" name="diseases" rows="3"
                                          placeholder="List any chronic conditions or allergies"><?php echo htmlspecialchars($userData['Diseases'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Health Info</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="col-md-6 mb-4">
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-phone-alt"></i> Emergency Contact</h5>
                    </div>
                    <div class="card-body">
                        <form action="updatedata.php" method="post">
                            <input type="hidden" name="action" value="update_emergency">
                            <div class="mb-3">
                                <label for="emergencyName" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergencyName" name="emergencyName" 
                                       value="<?php echo htmlspecialchars($emergencyData['EmergencyContactName'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="emergencyPhone" class="form-label">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" id="emergencyPhone" name="emergencyPhone" 
                                       value="<?php echo htmlspecialchars($emergencyData['EmergencyContactPhone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="doctorName" class="form-label">Doctor's Name</label>
                                <input type="text" class="form-control" id="doctorName" name="doctorName" 
                                       value="<?php echo htmlspecialchars($emergencyData['DoctorName'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="doctorPhone" class="form-label">Doctor's Phone</label>
                                <input type="tel" class="form-control" id="doctorPhone" name="doctorPhone" 
                                       value="<?php echo htmlspecialchars($emergencyData['DoctorPhone'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Emergency Info</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- App Settings -->
            <div class="col-md-6 mb-4">
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> App Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="updatedata.php" method="post">
                            <input type="hidden" name="action" value="update_settings">
                            <div class="mb-4">
                                <label class="form-label">Theme</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="theme-preview theme-light"></div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="lightTheme" 
                                                   value="light" <?php echo ($userData['ThemePreference'] == 'light') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="lightTheme">Light Theme</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="theme-preview theme-dark"></div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="darkTheme" 
                                                   value="dark" <?php echo ($userData['ThemePreference'] == 'dark') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="darkTheme">Dark Theme</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notifications" name="notifications" 
                                           <?php echo ($userData['NotificationEnabled'] ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="notifications">Enable Notifications</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 