<?php
require_once "conn.php";

if(isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    
    $sql = "SELECT * FROM Meals WHERE MealID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $meal = $result->fetch_assoc();
    } else {
        die("Meal not found");
    }
}

if(isset($_POST['update'])) {
    $id = sanitize_input($_POST['MealID']);
    $MealType = sanitize_input($_POST['MealType']);
    $FoodItems = sanitize_input($_POST['FoodItems']);
    $Calories = sanitize_input($_POST['Calories']);
    $Protein = sanitize_input($_POST['Protein']);
    $Carbs = sanitize_input($_POST['Carbs']);
    $Fat = sanitize_input($_POST['Fat']);
    $Date = sanitize_input($_POST['Date']);

    if($MealType != "" && $FoodItems != "" && $Calories != "" && $Date != "") {
        $sql = "UPDATE Meals SET MealType=?, FoodItems=?, Calories=?, Protein=?, Carbs=?, Fat=?, Date=? WHERE MealID=?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ssidddsi", $MealType, $FoodItems, $Calories, $Protein, $Carbs, $Fat, $Date, $id);
        
        if($stmt->execute()) {
            header("Location: diet.php");
            exit();
        } else {
            die("Error updating meal: " . $stmt->error);
        }
    } else {
        die("Required fields are missing!");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Meal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Meal</h2>
        <form method="POST">
            <input type="hidden" name="MealID" value="<?php echo $meal['MealID']; ?>">
            
            <div class="mb-3">
                <label for="MealType" class="form-label">Meal Type</label>
                <select name="MealType" class="form-control" required>
                    <option value="Breakfast" <?php echo ($meal['MealType'] == 'Breakfast') ? 'selected' : ''; ?>>Breakfast</option>
                    <option value="Lunch" <?php echo ($meal['MealType'] == 'Lunch') ? 'selected' : ''; ?>>Lunch</option>
                    <option value="Dinner" <?php echo ($meal['MealType'] == 'Dinner') ? 'selected' : ''; ?>>Dinner</option>
                    <option value="Snack" <?php echo ($meal['MealType'] == 'Snack') ? 'selected' : ''; ?>>Snack</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="FoodItems" class="form-label">Food Items</label>
                <textarea name="FoodItems" class="form-control" required><?php echo htmlspecialchars($meal['FoodItems']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="Calories" class="form-label">Calories</label>
                <input type="number" name="Calories" class="form-control" value="<?php echo htmlspecialchars($meal['Calories']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Protein" class="form-label">Protein (g)</label>
                <input type="number" step="0.1" name="Protein" class="form-control" value="<?php echo htmlspecialchars($meal['Protein']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Carbs" class="form-label">Carbs (g)</label>
                <input type="number" step="0.1" name="Carbs" class="form-control" value="<?php echo htmlspecialchars($meal['Carbs']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Fat" class="form-label">Fat (g)</label>
                <input type="number" step="0.1" name="Fat" class="form-control" value="<?php echo htmlspecialchars($meal['Fat']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Date" class="form-label">Date</label>
                <input type="date" name="Date" class="form-control" value="<?php echo htmlspecialchars($meal['Date']); ?>" required>
            </div>

            <button type="submit" name="update" class="btn btn-primary">Update Meal</button>
            <a href="diet.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 