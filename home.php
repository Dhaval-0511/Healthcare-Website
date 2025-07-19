<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Tracker - Your Personal Health Companion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            padding: 100px 0;
            position: relative;
        }
        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .cta-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }
        .theme-toggle {
            position: absolute;
            right: 20px;
            top: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1000;
            padding: 10px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        [data-theme="dark"] body {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        [data-theme="dark"] .feature-card {
            background-color: #2d2d2d;
            color: #ffffff;
        }
        [data-theme="dark"] .cta-section {
            background-color: #2d2d2d;
            color: #ffffff;
        }
        [data-theme="dark"] .text-muted {
            color: #a0a0a0 !important;
        }
        [data-theme="dark"] .card {
            background-color: #2d2d2d;
        }
        [data-theme="dark"] .bg-dark {
            background-color: #1a1a1a !important;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <button class="theme-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
            <i id="darkModeIcon" class="fas fa-moon"></i>
        </button>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-4">Track Your Health Journey</h1>
                    <p class="lead mb-4">Monitor your daily activities, water intake, nutrition, and more with our comprehensive health tracking platform.</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="register.php" class="btn btn-light btn-lg px-4 me-md-2">Get Started</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <img src="https://via.placeholder.com/600x400" alt="Health Tracking" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Features</h2>
                <p class="text-muted">Everything you need to maintain a healthy lifestyle</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-tint feature-icon text-primary"></i>
                            <h4>Water Tracking</h4>
                            <p class="text-muted">Monitor your daily water intake and stay hydrated throughout the day.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-running feature-icon text-success"></i>
                            <h4>Activity Tracking</h4>
                            <p class="text-muted">Log your exercises and track calories burned during workouts.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-utensils feature-icon text-danger"></i>
                            <h4>Nutrition Monitoring</h4>
                            <p class="text-muted">Keep track of your meals and maintain a balanced diet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-4">Start Your Health Journey Today</h2>
                    <p class="lead mb-4">Join thousands of users who have already transformed their lives with our health tracking platform.</p>
                    <a href="register.php" class="btn btn-primary btn-lg">Create Free Account</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 Health Tracker. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</body>
</html> 