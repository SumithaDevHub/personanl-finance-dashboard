<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $query = "INSERT INTO income (user_id, title, amount, date) VALUES (:user_id, :title, :amount, :date)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':date', $date);

    if ($stmt->execute()) {
        header('Location: income.php');
        exit;
    } else {
        $error_message = "Failed to add income";
    }
}

$query_recent_income = "SELECT * FROM income WHERE user_id = :user_id ORDER BY date DESC LIMIT 5";
$stmt_recent_income = $conn->prepare($query_recent_income);
$stmt_recent_income->bindParam(':user_id', $user_id);
$stmt_recent_income->execute();
$recent_income = $stmt_recent_income->fetchAll(PDO::FETCH_ASSOC);

$query_total_income = "SELECT SUM(amount) AS total_income FROM income WHERE user_id = :user_id";
$stmt_total_income = $conn->prepare($query_total_income);
$stmt_total_income->bindParam(':user_id', $user_id);
$stmt_total_income->execute();
$total_income_result = $stmt_total_income->fetch(PDO::FETCH_ASSOC);
$total_income = $total_income_result['total_income'] ?? 0;

function formatAmountINR($amount) {
    return '₹' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
}

.sidebar {
    width: 200px;
    background-color: #333;
    color: white;
    position: fixed;
    height: 100%;
    padding-top: 20px;
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    padding: 10px;
    text-align: center;
}

.sidebar ul li a {
    color: white;
    text-decoration: none;
}

.sidebar ul li a:hover {
    background-color: #45a049;
    display: block;
}

.content {
    margin-left: 220px;
    padding: 20px;
}

.totals, .add-form, .recent-transactions {
    margin-bottom: 20px;
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.add-form input, .add-form select, .add-form button {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

.add-form button {
    background-color: #4CAF50;
    color: white;
    cursor: pointer;
}

.add-form button:hover {
    background-color: #45a049;
}

.shaded-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.shaded-table th, .shaded-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.shaded-table th {
    background-color: #4CAF50;
    color: white;
}

.shaded-table td {
    background-color: white;
}

.recent-transactions ul {
    list-style-type: none;
    padding: 0;
}

.recent-transactions li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;
}

.recent-transactions li:last-child {
    border-bottom: none;
}

</style>
<body>
    <div class="sidebar">
        <div class="profile">
            <p>Hello, <?php echo $_SESSION['username']; ?></p>
        </div>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="expense.php">Expense</a></li>
            <li><a href="income.php">Income</a></li>
            <li><a href="view_transaction.php">View Transactions</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h2>Income</h2>
        <div class="totals">
            <h3>Total Income: <?php echo formatAmountINR($total_income); ?></h3>
        </div>
        <div class="add-form">
            <h3>Add New Income Entry</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="title" placeholder="Title" required>
                <input type="number" step="0.01" min="0" name="amount" placeholder="Amount" required>
                <input type="date" name="date" required>
                <button type="submit">Add Income</button>
            </form>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </div>
        <div class="recent-transactions">
            <h3>Recent Income Transactions</h3>
            <table class="shaded-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_income as $income): ?>
                    <tr>
                        <td><?php echo $income['title']; ?></td>
                        <td><?php echo formatAmountINR($income['amount']); ?></td>
                        <td><?php echo $income['date']; ?></td>
                        <td><a href="delete_income.php?id=<?php echo $income['income_id']; ?>">Delete</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
