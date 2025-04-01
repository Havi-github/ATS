<?php 
include 'db.php';  
$username = 'human'; 
$password = 'P@55w0rd';  

// Check if username already exists
$query = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$query->execute([$username]);
$usernameExists = $query->fetchColumn();

if ($usernameExists > 0) {
    // Username exists, display a message
    $message = "The username '$username' already exists. Please choose a different one.";
    $messageClass = "error";  // CSS class for error messages
} else {
    // Hash the password before storing it in the database 
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);  
    
    $query = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)"); 
    $query->execute([$username, $hashed_password]);

    // Success message
    $message = "User '$username' created successfully!";
    $messageClass = "success";  // CSS class for success messages
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Creation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .message {
            padding: 20px;
            border-radius: 8px;
            font-size: 1.2em;
            text-align: center;
            width: 60%;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .success {
            background-color: #28a745;
            color: white;
        }
        .error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="message <?php echo $messageClass; ?>">
        <?php echo $message; ?>
    </div>
</body>
</html>
