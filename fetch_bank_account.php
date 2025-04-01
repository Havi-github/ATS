<?php
include 'db.php';

if (isset($_POST['employee_id'])) {
    header('Content-Type: application/json');
    $employee_id = $_POST['employee_id'];

    // Prepare and execute the query to fetch bank account based on employee ID
    $stmt = $pdo->prepare("SELECT bank_account FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    $bank_account = $stmt->fetchColumn();


    echo $bank_account;
}