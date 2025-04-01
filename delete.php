<?php
include 'db.php';

// Fetch employee_id, payment_type_id, and reason from query parameters
$employee_id = $_GET['employee_id'] ?? null;
$payment_type_id = $_GET['payment_type_id'] ?? null;
$reason = $_GET['reason'] ?? null;

// Check if the necessary parameters are provided
if (!$employee_id || !$payment_type_id || !$reason) {
    // Redirect to index if any required parameter is missing
    header('Location: index.php');
    exit();
}

// Set the correct character encoding for Amharic text (UTF-8)
$pdo->exec("SET NAMES 'utf8mb4'");

// Prepare and execute the delete query
$query = $pdo->prepare("DELETE FROM attendance WHERE employee_id = ? AND payment_type_id = ? AND reason = ?");
$query->execute([$employee_id, $payment_type_id, $reason]);

// Redirect back to the index page after deletion
header('Location: index.php');
exit();
?>
