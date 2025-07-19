<?php

// Function to get nutritional information for a food item
function getNutritionalInfo($foodName, $quantity) {
    // You would typically use an API like Nutritionix, Edamam, or USDA Food Database here
    // For this example, we'll use a simple calculation based on common foods
    
    // Simplified database of common foods (per 100g/ml)
    $foodDatabase = [
        'rice' => ['calories' => 130, 'protein' => 2.7, 'fat' => 0.3],
        'chicken' => ['calories' => 165, 'protein' => 31, 'fat' => 3.6],
        'egg' => ['calories' => 155, 'protein' => 13, 'fat' => 11],
        'milk' => ['calories' => 42, 'protein' => 3.4, 'fat' => 1],
        'apple' => ['calories' => 52, 'protein' => 0.3, 'fat' => 0.2],
        'banana' => ['calories' => 89, 'protein' => 1.1, 'fat' => 0.3],
        'bread' => ['calories' => 265, 'protein' => 9.4, 'fat' => 3.2],
        'potato' => ['calories' => 77, 'protein' => 2, 'fat' => 0.1],
        // Add more foods as needed
    ];
    
    // Convert food name to lowercase for matching
    $foodName = strtolower($foodName);
    
    // Parse quantity (assuming format like "100g" or "200ml")
    preg_match('/(\d+)\s*([a-zA-Z]+)/', $quantity, $matches);
    if (count($matches) < 3) {
        return ['calories' => 0, 'protein' => 0, 'fat' => 0];
    }
    
    $amount = floatval($matches[1]);
    $unit = strtolower($matches[2]);
    
    // Convert to 100g/ml base
    $ratio = $amount / 100;
    
    // Find closest matching food
    $bestMatch = null;
    $highestSimilarity = 0;
    
    foreach ($foodDatabase as $knownFood => $nutrients) {
        $similarity = similar_text($foodName, $knownFood, $percent);
        if ($percent > $highestSimilarity) {
            $highestSimilarity = $percent;
            $bestMatch = $knownFood;
        }
    }
    
    // If no good match found (similarity < 50%), return zeros
    if ($highestSimilarity < 50) {
        return ['calories' => 0, 'protein' => 0, 'fat' => 0];
    }
    
    // Calculate nutrients based on quantity
    $nutrients = $foodDatabase[$bestMatch];
    return [
        'calories' => round($nutrients['calories'] * $ratio, 1),
        'protein' => round($nutrients['protein'] * $ratio, 1),
        'fat' => round($nutrients['fat'] * $ratio, 1)
    ];
}

// Function to analyze health status based on calorie ratio
function analyzeHealthStatus($caloriesBurned, $caloriesGained) {
    $ratio = $caloriesBurned / ($caloriesGained ?: 1);
    
    $status = [];
    
    // Check calorie balance
    if ($ratio < 0.5) {
        $status[] = "high risk of weight gain";
        if ($caloriesGained > 3000) {
            $status[] = "high bp risk";
        }
    } elseif ($ratio > 2) {
        $status[] = "excessive calorie deficit";
        $status[] = "low bp risk";
    }
    
    // Check absolute values
    if ($caloriesGained < 1200) {
        $status[] = "low blood sugar risk";
    } elseif ($caloriesGained > 4000) {
        $status[] = "diabetes risk";
    }
    
    if (empty($status)) {
        return "Normal health status";
    }
    
    return implode(", ", $status);
}

// Function to update daily calorie summary
function updateCalorieSummary($conn, $date) {
    // Get total calories gained from food
    $sql = "SELECT SUM(Calories) as total_gained FROM FoodItems WHERE Date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $caloriesGained = $result->fetch_assoc()['total_gained'] ?? 0;
    
    // Get total calories burned from activities
    $sql = "SELECT SUM(CaloriesBurned) as total_burned FROM Activities WHERE Date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $caloriesBurned = $result->fetch_assoc()['total_burned'] ?? 0;
    
    // Calculate health status
    $healthStatus = analyzeHealthStatus($caloriesBurned, $caloriesGained);
    
    // Update or insert daily summary
    $sql = "INSERT INTO CaloriesSummary (Date, CaloriesGained, CaloriesBurned, HealthStatus) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            CaloriesGained = VALUES(CaloriesGained),
            CaloriesBurned = VALUES(CaloriesBurned),
            HealthStatus = VALUES(HealthStatus)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdds", $date, $caloriesGained, $caloriesBurned, $healthStatus);
    $stmt->execute();
}
?> 