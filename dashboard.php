<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Query to fetch total income
$query_total_income = "SELECT SUM(amount) AS total_income FROM income WHERE user_id = :user_id";
$stmt_income = $conn->prepare($query_total_income);
$stmt_income->bindParam(':user_id', $user_id);
$stmt_income->execute();
$total_income = $stmt_income->fetch(PDO::FETCH_ASSOC)['total_income'];

// Query to fetch total expenses
$query_total_expenses = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE user_id = :user_id";
$stmt_expenses = $conn->prepare($query_total_expenses);
$stmt_expenses->bindParam(':user_id', $user_id);
$stmt_expenses->execute();
$total_expenses = $stmt_expenses->fetch(PDO::FETCH_ASSOC)['total_expenses'];

// Calculate total savings
$total_savings = $total_income - $total_expenses;

// Function to format amount with INR currency
function formatAmountINR($amount) {
    return '₹ ' . number_format($amount, 2);
}

// Handle delete transaction
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];
    
    if ($type == 'income') {
        $query_delete = "DELETE FROM income WHERE income_id = :id AND user_id = :user_id";
    } elseif ($type == 'expense') {
        $query_delete = "DELETE FROM expenses WHERE expense_id = :id AND user_id = :user_id";
    }
    
    $stmt_delete = $conn->prepare($query_delete);
    $stmt_delete->bindParam(':id', $id);
    $stmt_delete->bindParam(':user_id', $user_id);
    
    if ($stmt_delete->execute()) {
        // Redirect to refresh after delete
        header('Location: dashboard.php');
        exit;
    } else {
        // Handle delete error
        $delete_error = "Failed to delete transaction.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
    <style>
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
            display: flex;
            flex-direction: column;
            gap: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .totals {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
        }
        .charts {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
        }
        .transactions-list {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 2;
            overflow-x: auto;
        }
        .transactions-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .transactions-list table th,
        .transactions-list table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .transactions-list table th {
            background-color: #4CAF50;
            color: white;
        }
        .transactions-list table td {
            background-color: white;
        }
        .transactions-list table td.actions {
            text-align: center;
        }
        .transactions-list table td.actions a {
            color: #f44336;
            text-decoration: none;
            cursor: pointer;
        }
        .transactions-list table td.actions a:hover {
            text-decoration: underline;
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
        <div class="totals">
            <h2>Total Summary</h2>
            <div>
                <h3>Total Income: <?php echo formatAmountINR($total_income); ?></h3>
                <h3>Total Expenses: <?php echo formatAmountINR($total_expenses); ?></h3>
                <h3>Total Savings: <?php echo formatAmountINR($total_savings); ?></h3>
            </div>
        </div>
        <div class="charts">
            <h2>Financial Overview</h2>
            <canvas id="myChart"></canvas>
        </div>
        <div class="transactions-list">
            <h2>Recent Transactions</h2>
            <table>
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
                    <?php
                    // Fetch all transactions
                    $query_transactions = "SELECT 'income' AS type, income_id AS id, title, amount, date FROM income WHERE user_id = :user_id
                                            UNION ALL
                                            SELECT 'expense' AS type, expense_id AS id, title, amount, date FROM expenses WHERE user_id = :user_id
                                            ORDER BY date DESC";
                    $stmt_transactions = $conn->prepare($query_transactions);
                    $stmt_transactions->bindParam(':user_id', $user_id);
                    if ($stmt_transactions->execute()) {
                        $transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($transactions as $transaction) {
                            echo '<tr>';
                            echo '<td>' . ucfirst($transaction['type']) . '</td>';
                            echo '<td>' . $transaction['title'] . '</td>';
                            echo '<td>' . formatAmountINR($transaction['amount']) . '</td>';
                            echo '<td>' . $transaction['date'] . '</td>';
                            echo '<td class="actions">';
                            echo '<a href="dashboard.php?action=delete&type=' . $transaction['type'] . '&id=' . $transaction['id'] . '">Delete</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        // Handle query execution error
                        echo '<tr><td colspan="5">Failed to fetch transactions.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Get PHP variables into JavaScript
        var totalIncome = <?php echo $total_income; ?>;
        var totalExpenses = <?php echo $total_expenses; ?>;
        var totalSavings = <?php echo $total_savings; ?>;

        // Chart.js code to render a basic bar chart
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total Income', 'Total Expenses', 'Total Savings'],
                datasets: [{
                    label: 'Amount (₹)',
                    data: [totalIncome, totalExpenses, totalSavings],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
