<?php
require_once "conn.php";
session_start();

// For demo purposes, using a fixed UserID (you should get this from session after user login)
$userId = 1;
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Tracking - Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dark-mode.css" rel="stylesheet">
    <style>
        .meal-card {
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress {
            height: 25px;
            border-radius: 12px;
        }
        .meal-icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <!-- Main Content -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Food Tracking</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFoodModal">
                    <i class="fas fa-plus"></i> Add Food Item
                </button>
            </div>
            <div class="card-body">
                <!-- Calories Summary -->
                <?php
                $sql = "SELECT CaloriesGained, CaloriesBurned FROM Calories WHERE UserID = ? AND Date = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $userId, $today);
                $stmt->execute();
                $result = $stmt->get_result();
                $caloriesData = $result->fetch_assoc();
                
                $caloriesGained = $caloriesData['CaloriesGained'] ?? 0;
                $caloriesBurned = $caloriesData['CaloriesBurned'] ?? 0;
                $caloriesGoal = 2000; // Default daily calorie goal
                $progress = ($caloriesGained / $caloriesGoal) * 100;
                ?>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h4>Today's Calorie Summary</h4>
                        <div class="progress mb-2">
                            <div class="progress-bar <?php echo ($progress > 100) ? 'bg-danger' : 'bg-success'; ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo min(100, $progress); ?>%">
                                <?php echo $caloriesGained; ?> / <?php echo $caloriesGoal; ?> cal
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h5>Calories Gained</h5>
                                <p class="h3 text-success"><?php echo $caloriesGained; ?></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Calories Burned</h5>
                                <p class="h3 text-danger"><?php echo $caloriesBurned; ?></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Net Calories</h5>
                                <p class="h3 <?php echo ($caloriesGained - $caloriesBurned > $caloriesGoal) ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $caloriesGained - $caloriesBurned; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Meals -->
                <h4>Today's Meals</h4>
                <?php
                $mealTypes = ['Breakfast', 'Lunch', 'Dinner', 'Snacks'];
                foreach ($mealTypes as $mealType) {
                    $sql = "SELECT m.MealLogID, m.Quantity, f.FoodName, f.CaloriesPerServing, 
                                  (m.Quantity * f.CaloriesPerServing) as TotalCalories
                           FROM MealLogs m 
                           JOIN Food f ON m.FoodID = f.FoodID 
                           WHERE m.UserID = ? AND m.Date = ? AND m.MealType = ?
                           ORDER BY m.CreatedAt DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $userId, $today, $mealType);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    ?>
                    
                    <div class="meal-card card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-utensils meal-icon"></i>
                                <?php echo $mealType; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Food Item</th>
                                                <th>Quantity</th>
                                                <th>Calories</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['FoodName']; ?></td>
                                                    <td><?php echo $row['Quantity']; ?></td>
                                                    <td><?php echo $row['TotalCalories']; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                onclick="editFood(<?php echo $row['MealLogID']; ?>, '<?php echo $row['FoodName']; ?>', <?php echo $row['Quantity']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="deletedata.php?type=food&id=<?php echo $row['MealLogID']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Are you sure you want to delete this item?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No food items recorded for <?php echo $mealType; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Add Food Modal -->
    <div class="modal fade" id="addFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Food Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="adddata.php" method="post">
                        <input type="hidden" name="action" value="add_food">
                        <div class="mb-3">
                            <label for="mealType" class="form-label">Meal Type</label>
                            <select name="mealType" id="mealType" class="form-select" required>
                                <option value="">Select Meal Type</option>
                                <option value="Breakfast">Breakfast</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Dinner">Dinner</option>
                                <option value="Snacks">Snacks</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="foodName" class="form-label">Food Item</label>
                            <select name="foodName" id="foodName" class="form-select" required>
                                <option value="">Select Food Item</option>
                                <?php
                                $sql = "SELECT FoodName FROM Food ORDER BY FoodName";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['FoodName'] . "'>" . $row['FoodName'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity (servings)</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" 
                                   step="0.5" min="0.5" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_food" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Food Modal -->
    <div class="modal fade" id="editFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Food Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="updatedata.php" method="post">
                        <input type="hidden" name="action" value="update_food">
                        <input type="hidden" name="meal_log_id" id="edit_meal_log_id">
                        <div class="mb-3">
                            <label for="edit_food_name" class="form-label">Food Item</label>
                            <input type="text" id="edit_food_name" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity (servings)</label>
                            <input type="number" name="quantity" id="edit_quantity" 
                                   class="form-control" step="0.5" min="0.5" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_food" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dark-mode.js"></script>
    <script>
        function editFood(mealLogId, foodName, quantity) {
            document.getElementById('edit_meal_log_id').value = mealLogId;
            document.getElementById('edit_food_name').value = foodName;
            document.getElementById('edit_quantity').value = quantity;
            new bootstrap.Modal(document.getElementById('editFoodModal')).show();
        }
    </script>
</body>
</html> 