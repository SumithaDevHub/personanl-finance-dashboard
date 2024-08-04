<?php
session_start();
// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="sidebar">
        <!-- <div class="profile">
            <p>Hello, <?php echo $_SESSION['username']; ?></p>
        </div> -->
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="expense.php">Expense</a></li>
            <li><a href="income.php">Income</a></li>
            <li><a href="view_transaction.php">View Transactions</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <!-- Your dashboard content goes here -->
        <h2>Welcome to your Personal Finance Dashboard</h2>
        <!-- Add more content as per your dashboard design -->
    </div>
</body>
</html>
