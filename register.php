<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    
    // Check if username already exists
    $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_user) {
        $error_message = "Username already exists";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user into database
        $query = "INSERT INTO users (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error_message = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .register-container input[type="text"],
        .register-container input[type="password"],
        .register-container input[type="email"],
        .register-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 16px;
        }
        .register-container button {
            background-color: #4caf50;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .register-container button:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">Register</button>
        </form>
        <p style="text-align: center;">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
