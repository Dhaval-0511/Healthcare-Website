<?php
require_once "conn.php";
require_once "nutrition_api.php"; // We'll create this file next
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-5">Diet Tracking</h1>

        <!-- Water Intake Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Water Intake Tracking</h3>
            </div>
            <div class="card-body">
                <form action="add_water.php" method="post" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="number" name="amount" class="form-control" placeholder="Amount (ml)" required min="1">
                        </div>
                        <div class="col-md-4">
                            <input type="time" name="time" class="form-control" required value="<?php echo date('H:i'); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Add Water Intake</button>
                        </div>
                    </div>
                </form>

                <!-- Water Intake Table -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Amount (ml)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $today = date('Y-m-d');
                        $sql = "SELECT * FROM WaterIntake WHERE Date = ? ORDER BY Time DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $today);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('h:i A', strtotime($row['Time'])) . "</td>";
                            echo "<td>" . $row['Amount'] . " ml</td>";
                            echo "<td>
                                    <a href='edit_water.php?id=" . $row['WaterIntakeID'] . "' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a>
                                    <a href='delete_water.php?id=" . $row['WaterIntakeID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Food Tracking Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Food Tracking</h3>
            </div>
            <div class="card-body">
                <form action="add_food.php" method="post" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="foodName" class="form-control" placeholder="Food Item" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="quantity" class="form-control" placeholder="Quantity" required>
                        </div>
                        <div class="col-md-3">
                            <select name="mealType" class="form-control" required>
                                <option value="">Select Meal Type</option>
                                <option value="Breakfast">Breakfast</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Dinner">Dinner</option>
                                <option value="Snack">Snack</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Add Food Item</button>
                        </div>
                    </div>
                </form>

                <!-- Food Items Table -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Food Item</th>
                            <th>Quantity</th>
                            <th>Meal Type</th>
                            <th>Protein (g)</th>
                            <th>Fat (g)</th>
                            <th>Calories</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM FoodItems WHERE Date = ? ORDER BY CreatedAt DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $today);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('h:i A', strtotime($row['CreatedAt'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['FoodName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
                            echo "<td>" . $row['MealType'] . "</td>";
                            echo "<td>" . number_format($row['Protein'], 1) . "</td>";
                            echo "<td>" . number_format($row['Fat'], 1) . "</td>";
                            echo "<td>" . number_format($row['Calories'], 1) . "</td>";
                            echo "<td>
                                    <a href='edit_food.php?id=" . $row['FoodItemID'] . "' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a>
                                    <a href='delete_food.php?id=" . $row['FoodItemID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Health Status Section -->
        <?php
        // Get today's calorie summary
        $sql = "SELECT * FROM CaloriesSummary WHERE Date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();

        if ($summary) {
            $ratio = $summary['CaloriesBurned'] / ($summary['CaloriesGained'] ?: 1);
            $status = $summary['HealthStatus'];
            
            $alertClass = 'alert-info';
            if (strpos($status, 'high bp') !== false) {
                $alertClass = 'alert-danger';
            } elseif (strpos($status, 'low bp') !== false) {
                $alertClass = 'alert-warning';
            }
            
            echo "<div class='alert {$alertClass}' role='alert'>";
            echo "<h4 class='alert-heading'>Daily Health Status</h4>";
            echo "<p>Calories Gained: " . number_format($summary['CaloriesGained'], 1) . "</p>";
            echo "<p>Calories Burned: " . number_format($summary['CaloriesBurned'], 1) . "</p>";
            echo "<p>Burn/Gain Ratio: " . number_format($ratio, 2) . "</p>";
            echo "<p class='mb-0'><strong>Health Alert:</strong> " . htmlspecialchars($status) . "</p>";
            echo "</div>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 