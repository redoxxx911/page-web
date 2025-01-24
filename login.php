<?php
// login.php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username == 'adm' && $password == '123') {
        // Redirect to admin.php
        $_SESSION['username'] = 'adm'; // Store session for admin user
        header("Location: admin.php");
        exit();
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();
            if (password_verify($password, $user['password'])) {
                // Login successful, store user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to body_choice.php or another page
                header("Location: bs.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(110deg, #f9ac54 15%, #171b21 100%);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 1rem;
    }
    
    .container {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        width: 100%;;
        max-width: 400px;
        backdrop-filter: blur(10px);
        transition: transform 0.2s ease;
    }

    .container:hover {
        transform: translateY(-5px);
    }

    input {
        width: 100%;
        padding: 12px 16px;
        margin: 8px 0;
        border: 2px solid #eee;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    input:focus {
        outline: none;
        border-color: #111317;
        box-shadow: 0 0 0 3px rgba(17, 19, 23, 0.1);
    }

    button {
        width: 100%;
        padding: 12px;
        background: #111317;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #1a1f25;
    }

    h2 {
        margin-bottom: 1.5rem;
        color: #111317;
        font-weight: 600;
    }

    .error {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.1);
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
</style>
</head>
<body>
    <div class="container">
        <h2>LOGIN</h2>
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="login-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
