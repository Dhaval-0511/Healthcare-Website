<?php
session_start();
require_once "CRUD/conn.php";

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if(isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    
    // Validation
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all required fields";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username exists
        $check_sql = "SELECT UserID FROM Users WHERE Username = ? OR Email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO Users (Username, Password, Email, Name, Age, Weight) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssid", $username, $hashed_password, $email, $name, $age, $weight);
            
            if($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Create default settings
                $settings_sql = "INSERT INTO Settings (UserID) VALUES (?)";
                $settings_stmt = $conn->prepare($settings_sql);
                $settings_stmt->bind_param("i", $user_id);
                $settings_stmt->execute();
                
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .register-header h1 {
            color: #333;
            font-size: 24px;
        }
        .theme-toggle {
            position: absolute;
            right: 0;
            top: 0;
            background: none;
            border: none;
            color: inherit;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        .theme-toggle:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        [data-theme="dark"] .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        [data-theme="dark"] .register-container {
            background-color: #2d2d2d;
            color: #ffffff;
        }
        [data-theme="dark"] .register-header h1 {
            color: #ffffff;
        }
        [data-theme="dark"] .form-control {
            background-color: #333333;
            border-color: #404040;
            color: #ffffff;
        }
        [data-theme="dark"] .form-control:focus {
            background-color: #404040;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <button class="theme-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                    <i id="darkModeIcon" class="fas fa-moon"></i>
                </button>
                <h1>Create Account</h1>
            </div>
            
            <form method="POST" action="register_process.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Register</button>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 