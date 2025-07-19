<?php
require_once "conn.php";

// Create Meals table
$meals_table = "CREATE TABLE IF NOT EXISTS Meals (
    MealID INT AUTO_INCREMENT PRIMARY KEY,
    MealType VARCHAR(50) NOT NULL,
    FoodItems TEXT NOT NULL,
    Calories INT NOT NULL,
    Protein DECIMAL(10,2),
    Carbs DECIMAL(10,2),
    Fat DECIMAL(10,2),
    Date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($meals_table) === FALSE) {
    die("Error creating Meals table: " . $conn->error);
}

// Create WaterIntake table
$water_table = "CREATE TABLE IF NOT EXISTS WaterIntake (
    WaterIntakeID INT AUTO_INCREMENT PRIMARY KEY,
    Amount INT NOT NULL,
    Date DATE NOT NULL,
    Time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($water_table) === FALSE) {
    die("Error creating WaterIntake table: " . $conn->error);
}

// Create Activities table
$activities_table = "CREATE TABLE IF NOT EXISTS Activities (
    ActivityID INT AUTO_INCREMENT PRIMARY KEY,
    ActivityType VARCHAR(50) NOT NULL,
    Duration INT NOT NULL,
    CaloriesBurned INT NOT NULL,
    Date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($activities_table) === FALSE) {
    die("Error creating Activities table: " . $conn->error);
}

echo "Tables created successfully";
$conn->close();
?> 