<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    if ($type === 'income') {
        $query = "DELETE FROM income WHERE income_id = :id AND user_id = :user_id";
    } else if ($type === 'expense') {
        $query = "DELETE FROM expenses WHERE expense_id = :id AND user_id = :user_id";
    } else {
        // Invalid type
        header('Location: view_transaction.php');
        exit;
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        header('Location: view_transaction.php');
        exit;
    } else {
        echo "Error deleting transaction";
    }
} else {
    header('Location: view_transaction.php');
    exit;
}
?>
