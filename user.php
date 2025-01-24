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

// Fetch user progress
$sql = "SELECT * FROM user_progress WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user_progress = $stmt->fetch(PDO::FETCH_ASSOC);



// Fetch the foods the user has consumed today
$sql = "SELECT f.name, f.calories, f.protein, f.carbs, f.fat, ufi.quantity 
        FROM user_food_intake ufi
        INNER JOIN foods f ON ufi.food_id = f.id
        WHERE ufi.user_id = :user_id AND ufi.date = CURDATE()";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$food_intake = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available foods for selection
$sql = "SELECT * FROM foods";
$stmt = $pdo->query($sql);
$all_foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle food addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['food_id'])) {
    $food_id = $_POST['food_id'];
    $quantity = $_POST['quantity'] ?? 1;

    $sql = "INSERT INTO user_food_intake (user_id, food_id, quantity) 
            VALUES (:user_id, :food_id, :quantity)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':food_id', $food_id);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->execute();

    // Update user progress
    $sql = "SELECT calories, protein, carbs, fat FROM foods WHERE id = :food_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':food_id', $food_id);
    $stmt->execute();
    $food_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($food_data) {
        $calories = $food_data['calories'] * $quantity;
        $protein = $food_data['protein'] * $quantity;
        $carbs = $food_data['carbs'] * $quantity;
        $fat = $food_data['fat'] * $quantity;

        $sql = "UPDATE user_progress 
                SET calories_consumed = calories_consumed + :calories,
                    protein_consumed = protein_consumed + :protein,
                    carbs_consumed = carbs_consumed + :carbs,
                    fat_consumed = fat_consumed + :fat
                WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':calories', $calories);
        $stmt->bindParam(':protein', $protein);
        $stmt->bindParam(':carbs', $carbs);
        $stmt->bindParam(':fat', $fat);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    // Redirect to avoid resubmission
    header("Location: user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriTrack Dashboard</title>
    <link rel="stylesheet" href="userstyle.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .add-food-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }

        .add-food-form {
            display: grid;
            gap: 1.5rem;
            max-width: 500px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.9rem;
            color: #4a5568;
            font-weight: 500;
        }

        .form-group select,
        .form-group input {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #f8fafc;
        }

        .form-group select:hover,
        .form-group input:hover {
            border-color: #cbd5e0;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .add-food-btn {
            background-color:rgb(99, 65, 14);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .add-food-btn:hover {
            background-color: #2c5282;
            transform: translateY(-1px);
        }

        .add-food-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .add-food-section {
                padding: 1.5rem;
            }
            
            .add-food-form {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <span>fit&healthy</span>
        </div>
        <ul class="nav-links">
            <li class="active"><i class="fas fa-home"></i> Dashboard</li>
            <li ><i class="fas fa-utensils"></i> <a href="#meal">Meals</a></li>
            <li style="margin-top: auto; color: #ff4444; cursor: pointer;" onclick="window.location.href='logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </li>
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
            <h2>Your Progress</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Calories</h3>
                    <p class="stat-number"><?= $user_progress['calories_consumed'] ?> / <?= $user_progress['calories_goal'] ?></p> <br>
                </div>
                <div class="stat-card">
                    <h3>Protein</h3>
                    <p class="stat-number"><?= $user_progress['protein_consumed'] ?>g / <?= $user_progress['protein_goal'] ?>g</p>
                </div>
                <div class="stat-card">
                    <h3>Carbs</h3>
                    <p class="stat-number"><?= $user_progress['carbs_consumed'] ?>g / <?= $user_progress['carbs_goal'] ?>g</p>
                </div>
                <div class="stat-card">
                    <h3>Fat</h3>
                    <p class="stat-number"><?= $user_progress['fat_consumed'] ?>g / <?= $user_progress['fat_goal'] ?>g</p>
                </div>
            </div>
            <section id="meal" class="section__container class__container">
            <h2>Today's Meals</h2>
            <div class="meal-cards">
                <?php if (empty($food_intake)): ?>
                    <p>No food added yet. Add your meals below!</p>
                <?php else: ?>
                    <?php foreach ($food_intake as $food): ?>
                        <div class="meal-card">
                            <h3><?= htmlspecialchars($food['name']) ?></h3>
                            <p>Quantity: <?= $food['quantity'] ?></p>
                            <p>Calories: <?= $food['calories'] * $food['quantity'] ?></p>
                            <p>Protein: <?= $food['protein'] * $food['quantity'] ?>g</p>
                            <p>Carbs: <?= $food['carbs'] * $food['quantity'] ?>g</p>
                            <p>Fat: <?= $food['fat'] * $food['quantity'] ?>g</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="add-food-section">
    <h2>Add Food</h2>
    <form method="POST" class="add-food-form">
        <div class="form-group">
            <label for="food_id">Select Food:</label>
            <select name="food_id" id="food_id" required>
                <?php foreach ($all_foods as $food): ?>
                    <option value="<?= $food['id'] ?>"><?= htmlspecialchars($food['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="1" required>
        </div>

        <button type="submit" class="add-food-btn">Add Food</button>
    </form>
</div>
        </div>
    </main>
</body>
</html>
