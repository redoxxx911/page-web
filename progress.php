<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Get user ID from the database
$sql = "SELECT id FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User data not found.");
}

$user_id = $user['id'];

// Fetch the foods the user has consumed today
$sql = "SELECT f.name, f.calories, f.protein, f.carbs, f.fat, ufi.quantity 
        FROM user_food_intake ufi
        INNER JOIN foods f ON ufi.food_id = f.id
        WHERE ufi.user_id = :user_id AND ufi.date = CURDATE()";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$food_intake = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress - NutriTrack</title>
    <link rel="stylesheet" href="userstyle.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <span>NutriTrack</span>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="progress.php" class="active"><i class="fas fa-chart-line"></i> Progress</a></li>
            <li><i class="fas fa-utensils"></i> Meals</li>
            <li><i class="fas fa-cog"></i> Settings</li>
        </ul>
    </nav>

    <main class="main-content">
        <header>
            <div class="user-profile">
                <i class="fas fa-bell"></i>
                <div class="avatar"></div>
            </div>
        </header>

        <div class="dashboard">
            <h2>Your Daily Progress</h2>
            
            <?php if (empty($food_intake)): ?>
                <p>No food has been added today.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Food</th>
                            <th>Quantity</th>
                            <th>Calories</th>
                            <th>Protein</th>
                            <th>Carbs</th>
                            <th>Fat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($food_intake as $food): ?>
                            <tr>
                                <td><?= htmlspecialchars($food['name']) ?></td>
                                <td><?= $food['quantity'] ?></td>
                                <td><?= $food['calories'] * $food['quantity'] ?></td>
                                <td><?= $food['protein'] * $food['quantity'] ?>g</td>
                                <td><?= $food['carbs'] * $food['quantity'] ?>g</td>
                                <td><?= $food['fat'] * $food['quantity'] ?>g</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
