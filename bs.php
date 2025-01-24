<?php
session_start();

// Include the database connection file
require_once 'config.php';

// Check if the user is logged in by checking if the session has a username
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to the login page if the user is not logged in
    exit;
}

// Retrieve the username from the session
$username = $_SESSION['username'] ?? '';

// Retrieve data from the form submission
$corps = $_POST['corps'] ?? '';
$objectif = $_POST['objectif'] ?? '';

// Save data in session
$_SESSION['corps'] = $corps;
$_SESSION['objectif'] = $objectif;

// Insert the data into the database if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Retrieve the user_id using the username from the session
        $sql = "SELECT id FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if a user was found with the given username
        if ($user) {
            $user_id = $user['id'];

            // Insert the user's body choice and objective into the user_body_choices table
            $sql = "INSERT INTO user_body_choices (user_id, body_choice, objectif) 
                    VALUES (:user_id, :body_choice, :objectif)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':body_choice', $corps);
            $stmt->bindParam(':objectif', $objectif);

            // Execute the query
            $stmt->execute();

            header("Location: user.php ");
            exit;
        } else {
            echo "User not found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choix du corps et d'objectif</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
<div class="form-container">
    <h1>Choisissez votre corps et objectif</h1>

    <div class="body-types-grid">
        <div class="body-type-card">
            <img src="https://st5.depositphotos.com/5316818/66035/i/450/depositphotos_660359304-stock-photo-illustration-male-body-mesomorph-body.jpg" alt="Endomorphe" class="body-type-image">
            <h4>Endomorphe</h4>
            <p>Corps athlétique et musclé</p>
        </div>
        <div class="body-type-card">
            <img src="https://st5.depositphotos.com/5316818/66035/i/450/depositphotos_660359242-stock-photo-illustration-male-body-ectomorph-body.jpg" alt="Ectomorphe" class="body-type-image">
            <h4>Ectomorphe</h4>
            <p>Corps mince et élancé</p>
        </div>
        <div class="body-type-card">
            <img src="https://st5.depositphotos.com/5316818/66000/i/380/depositphotos_660008666-stock-photo-illustration-male-body-endomorph-body.jpg" alt="Mesomorphe" class="body-type-image">
            <h4>Mesomorphe</h4>
            <p>Corps naturellement rond et doux</p>
        </div>
    </div>

    <form  method="POST">
        <div class="form-group">
            <label for="corps">Type de corps :</label>
            <select id="corps" name="corps" required>
                <option value="endomorphe">Endomorphe</option>
                <option value="ectomorphe">Ectomorphe</option>
                <option value="mesomorphe">Mésomorphe</option>
            </select>
        </div>
        <div class="form-group">
            <label for="objectif">Objectif :</label>
            <select id="objectif" name="objectif" required>
                <option value="perte_poids">Perte de poids</option>
                <option value="prise_masse">Prise de masse</option>
                <option value="tonifier">Tonifier</option>
            </select>
        </div>
        <button type="submit" class="submit-btn">Voir mon programme</button>
    </form>
</div>
</body>
</html>
