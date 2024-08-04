<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all transactions
$query_transactions = "SELECT 'income' AS type, income_id AS id, title, amount, date FROM income WHERE user_id = :user_id
                        UNION ALL
                        SELECT 'expense' AS type, expense_id AS id, title, amount, date FROM expenses WHERE user_id = :user_id
                        ORDER BY date DESC";
$stmt_transactions = $conn->prepare($query_transactions);
$stmt_transactions->bindParam(':user_id', $user_id);
if ($stmt_transactions->execute()) {
    $transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Handle query execution error
    $transactions = array(); // Initialize as empty array if query fails
}

// Function to format amount in INR
function formatAmountINR($amount) {
    return '₹' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <style>
        .shaded-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .shaded-table thead {
            background-color: #4CAF50;
            color: white;
        }
        .shaded-table th,
        .shaded-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .shaded-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .sidebar {
            width: 200px;
            float: left;
            background: #333;
            color: #fff;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            height: 100vh;
        }
        .profile {
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="profile">
            <p>Hello, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></p>
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
        <h2>View Transactions</h2>
        <div class="transactions-list">
            <table class="shaded-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo ucfirst($transaction['type']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                            <td><?php echo formatAmountINR($transaction['amount']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                            <td><a href="delete_transaction.php?type=<?php echo $transaction['type']; ?>&id=<?php echo $transaction['id']; ?>">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
