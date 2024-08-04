<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle adding new expense
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category = $_POST['category'];

    $query = "INSERT INTO expenses (user_id, title, amount, date, category) VALUES (:user_id, :title, :amount, :date, :category)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':category', $category);

    if ($stmt->execute()) {
        // Redirect to expense.php after successful insertion
        header('Location: expense.php');
        exit;
    } else {
        // Handle insertion failure
        $error_message = "Failed to add expense";
    }
}

// Fetch recent expense transactions
$query_recent_expenses = "SELECT * FROM expenses WHERE user_id = :user_id ORDER BY date DESC LIMIT 5";
$stmt_recent_expenses = $conn->prepare($query_recent_expenses);
$stmt_recent_expenses->bindParam(':user_id', $user_id);
$stmt_recent_expenses->execute();
$recent_expenses = $stmt_recent_expenses->fetchAll(PDO::FETCH_ASSOC);

// Calculate total expense
$query_total_expense = "SELECT SUM(amount) AS total_expense FROM expenses WHERE user_id = :user_id";
$stmt_total_expense = $conn->prepare($query_total_expense);
$stmt_total_expense->bindParam(':user_id', $user_id);
$stmt_total_expense->execute();
$total_expense_result = $stmt_total_expense->fetch(PDO::FETCH_ASSOC);
$total_expense = $total_expense_result['total_expense'];

// Function to format amount with currency (INR)
function formatAmountINR($amount) {
    return '₹' . number_format($amount, 2);
}

// Handle delete expense
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $expense_id = $_GET['id'];
    
    $query_delete = "DELETE FROM expenses WHERE expense_id = :expense_id AND user_id = :user_id";
    $stmt_delete = $conn->prepare($query_delete);
    $stmt_delete->bindParam(':expense_id', $expense_id);
    $stmt_delete->bindParam(':user_id', $user_id);
    
    if ($stmt_delete->execute()) {
        // Redirect to expense.php after successful deletion
        header('Location: expense.php');
        exit;
    } else {
        // Handle delete failure
        $delete_error = "Failed to delete expense";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
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
</head>
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
        <div class="totals">
            <div class="summary">
                <h3>Total Expense</h3>
                <p><?php echo formatAmountINR($total_expense); ?></p>
            </div>
        </div>
        <div class="add-form">
            <h2>Add New Expense Entry</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="title" placeholder="Title" required>
                <input type="number" step="0.01" min="0" name="amount" placeholder="Amount" required>
                <input type="date" name="date" required>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Food">Food</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Entertainment">Entertainment</option>
                    <!-- Add more categories as needed -->
                </select>
                <button type="submit">Add Expense</button>
            </form>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </div>
        <div class="recent-transactions">
            <h2>Recent Expense Transactions</h2>
            <table class="shaded-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['title']; ?></td>
                        <td><?php echo formatAmountINR($expense['amount']); ?></td>
                        <td><?php echo $expense['date']; ?></td>
                        <td><?php echo $expense['category']; ?></td>
                        <td><a href="expense.php?action=delete&id=<?php echo $expense['expense_id']; ?>">Delete</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
