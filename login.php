<?php
session_start();
require_once "CRUD/conn.php";

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

// Handle login form submission
if(isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = "Please enter both username/email and password";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT UserID, Username, Password, Name FROM Users WHERE Username = ? OR Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if(password_verify($password, $user['Password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['name'] = $user['Name'];
                $_SESSION['welcome_message'] = true;
                
                // Redirect to index
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .login-header h1 {
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
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <button class="theme-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                    <i id="darkModeIcon" class="fas fa-moon"></i>
                </button>
                <h1>Health Tracker Login</h1>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary">Login</button>
                
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 