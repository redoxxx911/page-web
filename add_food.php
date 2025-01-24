<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'adm') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $calories = trim($_POST['calories']);
    $protein = trim($_POST['protein']);

    if (!empty($name) && is_numeric($calories) && is_numeric($protein)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO foods (name, calories, protein) VALUES (?, ?, ?)");
            $stmt->execute([$name, $calories, $protein]);
            $success_message = "Food item added successfully!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all fields with valid data.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food Item</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="main-content">
        <h1>Add New Food Item</h1>
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form action="add_food.php" method="POST">
            <div class="form-group">
                <label for="name">Food Name</label>
                <input type="text" name="name" id="name" placeholder="Enter food name" required>
            </div>
            <div class="form-group">
                <label for="calories">Calories (kcal)</label>
                <input type="number" name="calories" id="calories" placeholder="Enter calories" required>
            </div>
            <div class="form-group">
                <label for="protein">Protein (g)</label>
                <input type="number" name="protein" id="protein" placeholder="Enter protein" required>
            </div>
            <button type="submit" class="btn">Add Food</button>
        </form>
        <a href="admin.php" class="btn" style="margin-top: 1rem;">Back to Dashboard</a>
    </div>
</body>
</html>
