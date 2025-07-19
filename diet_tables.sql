-- Water Intake table
CREATE TABLE WaterIntake (
    WaterIntakeID INT PRIMARY KEY AUTO_INCREMENT,
    Amount INT NOT NULL, -- in ml
    Date DATE NOT NULL,
    Time TIME NOT NULL
);

-- Food Items table
CREATE TABLE FoodItems (
    FoodItemID INT PRIMARY KEY AUTO_INCREMENT,
    FoodName VARCHAR(100) NOT NULL,
    Quantity VARCHAR(50) NOT NULL,
    MealType ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack') NOT NULL,
    Date DATE NOT NULL,
    Protein FLOAT,
    Fat FLOAT,
    Calories FLOAT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Daily Calories Summary table
CREATE TABLE CaloriesSummary (
    SummaryID INT PRIMARY KEY AUTO_INCREMENT,
    Date DATE NOT NULL UNIQUE,
    CaloriesGained FLOAT DEFAULT 0,
    CaloriesBurned FLOAT DEFAULT 0,
    HealthStatus VARCHAR(100),
    LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add Water Intake table to existing database
ALTER TABLE results ADD COLUMN water_intake INT DEFAULT 0;
ALTER TABLE results ADD COLUMN water_time TIME;

-- Add Food tracking columns to results table
ALTER TABLE results ADD COLUMN food_name VARCHAR(100);
ALTER TABLE results ADD COLUMN quantity VARCHAR(50);
ALTER TABLE results ADD COLUMN meal_type ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack');
ALTER TABLE results ADD COLUMN protein FLOAT DEFAULT 0;
ALTER TABLE results ADD COLUMN fat FLOAT DEFAULT 0;
ALTER TABLE results ADD COLUMN calories FLOAT DEFAULT 0;
ALTER TABLE results ADD COLUMN health_status VARCHAR(100); 