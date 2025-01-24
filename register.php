<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        
        if($stmt->execute()) {
            $_SESSION['message'] = "Registration successful! Please login.";
            header("Location: login.php");
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(110deg, #f9ac54 15%, #171b21 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background-color: rgba(255, 255, 255, 0.97);
            padding: 3rem;
            border-radius: 15px;
            border-left: 5px solid #d79447;
            box-shadow: 0 10px 30px rgba(255, 107, 0, 0.1);
            width: 100%;
            max-width: 450px;
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 107, 0, 0.2);
        }
        
        h2 {
            color: #35373b;
            font-size: 2.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: #d79447;
            border-radius: 2px;
        }

        input, select {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 1.5rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f8f8;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #d79447;
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #d79447 0%, #d79447 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 0, 0.3);
            background: linear-gradient(135deg, #ff8800 0%, #ff6b00 100%);
        }

        button:active {
            transform: translateY(1px);
        }

        label {
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>