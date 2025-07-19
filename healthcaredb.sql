-- Create the database
CREATE DATABASE IF NOT EXISTS healthcare_db;
USE healthcare_db;

-- Users Table
CREATE TABLE IF NOT EXISTS Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Name VARCHAR(100),
    Age INT,
    Weight DECIMAL(5, 2),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Health Details Table
CREATE TABLE IF NOT EXISTS Health_Details (
    UserID INT PRIMARY KEY,
    Height DECIMAL(5, 2),
    BloodGroup VARCHAR(3),
    Diseases TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Activities Table
CREATE TABLE IF NOT EXISTS Activities (
    UserID INT,
    Date DATE,
    ActivityType VARCHAR(50),
    Duration INT,
    Steps INT,
    Distance DECIMAL(5, 2),
    CaloriesBurned INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, Date, ActivityType),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Food Table
CREATE TABLE IF NOT EXISTS Food (
    FoodID INT AUTO_INCREMENT PRIMARY KEY,
    FoodName VARCHAR(100) NOT NULL,
    CaloriesPerServing INT,
    ServingSize VARCHAR(50),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- MealLogs Table
CREATE TABLE IF NOT EXISTS MealLogs (
    UserID INT,
    Date DATE,
    MealType VARCHAR(50),
    FoodID INT,
    Quantity DECIMAL(5, 2),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, Date, MealType, FoodID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (FoodID) REFERENCES Food(FoodID)
);

-- Goals Table
CREATE TABLE IF NOT EXISTS Goals (
    UserID INT,
    GoalType VARCHAR(50),
    StartDate DATE,
    GoalValue DECIMAL(10, 2),
    EndDate DATE,
    Status VARCHAR(20) DEFAULT 'Active',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, GoalType, StartDate),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- EmergencyInfo Table
CREATE TABLE IF NOT EXISTS EmergencyInfo (
    UserID INT PRIMARY KEY,
    EmergencyContactName VARCHAR(100),
    EmergencyContactPhone VARCHAR(20),
    MedicalConditions TEXT,
    Allergies TEXT,
    Medications TEXT,
    DoctorName VARCHAR(100),
    DoctorPhone VARCHAR(20),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Settings Table
CREATE TABLE IF NOT EXISTS Settings (
    UserID INT PRIMARY KEY,
    ThemePreference VARCHAR(50) DEFAULT 'light',
    NotificationEnabled BOOLEAN DEFAULT TRUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Water Intake Table
CREATE TABLE IF NOT EXISTS Water_Intake (
    UserID INT,
    Date DATE,
    Amount DECIMAL(5, 2),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, Date),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Calories Table
CREATE TABLE IF NOT EXISTS Calories (
    UserID INT,
    Date DATE,
    CaloriesBurned INT,
    CaloriesGained INT,
    Ratio DECIMAL(10, 2),
    Prediction TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, Date),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

