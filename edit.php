<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}


include 'db.php';

$pdo->exec("SET NAMES 'utf8mb4'");

// Fetch employee and payment type from query parameters
$employee_id = $_GET['employee_id'] ?? null;
$payment_type_id = $_GET['payment_type_id'] ?? null;

if (!$employee_id || !$payment_type_id) {
    // Redirect to index if no valid ID is provided
    header('Location: index.php');
    exit();
}

// Fetch existing attendance records for the given employee and payment type
$query = $pdo->prepare("
    SELECT a.date, p.payment_type, e.name, a.reason, a.gender
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    JOIN payment_types p ON a.payment_type_id = p.id
    WHERE a.employee_id = ? AND a.payment_type_id = ?
");
$query->execute([$employee_id, $payment_type_id]);
$records = $query->fetchAll(PDO::FETCH_ASSOC);

$employees = $pdo->query("SELECT * FROM employees")->fetchAll(PDO::FETCH_ASSOC);
$payment_types = $pdo->query("SELECT * FROM payment_types")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for updating attendance records
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_payment_type_id = $_POST['payment_type_id'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $reason = $_POST['reason'];
    $gender = $_POST['gender'];

    // Convert dates to DateTime objects for processing
    $start = new DateTime($from_date);
    $end = new DateTime($to_date);

    // Delete old attendance records for this employee and payment type
    $delete_query = $pdo->prepare("DELETE FROM attendance WHERE employee_id = ? AND payment_type_id = ?");
    $delete_query->execute([$employee_id, $payment_type_id]);

    // Add new attendance records for the new date range and payment type
    $interval = new DateInterval('P1D'); // 1-day interval
    $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

    foreach ($dateRange as $date) {
        $formatted_date = $date->format('Y-m-d');

        // Check for duplicates
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->execute([$employee_id, $formatted_date]);
        $recordExists = $stmt->fetchColumn();

        if (!$recordExists) {
            // Insert new attendance record
            $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, payment_type_id, date, reason, gender) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $new_payment_type_id, $formatted_date, $reason, $gender]);
        }
    }

    // Redirect back to the index page after editing
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>በአዲስ አበባ ከተማ አስተዳደር ምግብና መድሃኒት ባለስልጣን</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Style for the search form */
        h2 {
            text-align: center;
        }

        .form-group {
            width: 40%;
            margin-left: 30%;
            padding: 10px;
            border-radius: 8px;
            /* Rounded corners */
            background-color: #fff;
            /* Background color */
            border: 1px solid #ccc;
            /* Light border */
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.1), -4px -4px 8px rgba(255, 255, 255, 0.5);
            /* 3D effect */
            transition: all 0.3s ease-in-out;
            /* Smooth transition for hover effect */
        }

        .form-group:hover {
            box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.2), -6px -6px 12px rgba(255, 255, 255, 0.4);
            /* Stronger shadow on hover */
            border-color: #007bff;
            /* Change border color on hover */
        }

    

        .btn {
            margin: 10px;
            margin-left: 45%;
            
        }

        nav.navbar {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            /* Gradient background */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Subtle shadow for depth */
        }

        .navbar-brand {
            margin-left: 4%;
            font-size: 1.5rem;
            font-weight: bold;
            color: #f8f9fa;
            /* Light color for contrast */
        }

        .nav-link {
            color: #f8f9fa !important;
            /* Light color for the nav links */
            font-weight: 500;
            margin-right: 1rem;
            transition: color 0.3s ease;
            /* Smooth color transition */
        }

        .nav-link:hover {
            color: #d1ecf1 !important;
            /* Hover effect */
        }

        .navbar-text {
            color: #f8f9fa;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <!-- <a href="index.php" class="navbar-brand" href="#">Home</a> -->
        <a href="index.php" class="btn btn-light ml-auto">Back to Records</a>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">ማስተካከያው የሚደረገው ለ -> <?= htmlspecialchars($records[0]['name']) ?></h2>
        <form method="POST">
            <div class="form-group">
                <label for="employee">የባልሙያ ስም</label>
                <select name="employee_id" class="form-control" disabled>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>" <?= $employee['id'] == $employee_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($employee['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="gender">ዖታ</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="male" value="ወንድ"
                        <?php
                        // Check if the employee's gender is 'ወንድ' (Male) and select the radio button
                        if (isset($records[0]['gender']) && $records[0]['gender'] == 'ወንድ') echo 'checked';
                        ?> required>
                    <label class="form-check-label" for="male">ወንድ</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="female" value="ሴት"
                        <?php
                        // Check if the employee's gender is 'ሴት' (Female) and select the radio button
                        if (isset($records[0]['gender']) && $records[0]['gender'] == 'ሴት') echo 'checked';
                        ?> required>
                    <label class="form-check-label" for="female">ሴት</label>
                </div>
            </div>


            <div class="form-group">
                <label>የክፍያ አይነት</label>
                <select name="payment_type_id" class="form-control" required>
                    <?php foreach ($payment_types as $payment_type): ?>
                        <option value="<?= $payment_type['id'] ?>"><?= htmlspecialchars($payment_type['payment_type']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fromDate">የቀን ብዛት</label>
                <div class="form-inline">
                    <input type="date" id="fromDate" name="from_date" class="form-control mr-2" value="<?= isset($_POST['from_date']) ? htmlspecialchars($_POST['from_date']) : '' ?>" required>
                    <span>to</span>
                    <input type="date" id="toDate" name="to_date" class="form-control ml-2" value="<?= isset($_POST['to_date']) ? htmlspecialchars($_POST['to_date']) : '' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="reason">የአበል ምክንያት</label>
                <textarea id="reason" name="reason" class="form-control" rows="3" placeholder="ሙሉ የአበሉን ምክንያት እዚህ ያስቀምጡ......."><?= isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically set the gender radio button when an employee is selected
        document.getElementById('employeeSelect').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var gender = selectedOption.getAttribute('data-gender'); // Get gender from the selected option

            // Set the gender radio button based on the fetched gender
            if (gender === 'ወንድ') {
                document.getElementById('male').checked = true;
            } else if (gender === 'ሴት') {
                document.getElementById('female').checked = true;
            }
        });

        // Automatically select gender for the first employee if already selected on page load
        document.addEventListener('DOMContentLoaded', function() {
            var selectedOption = document.getElementById('employeeSelect').options[document.getElementById('employeeSelect').selectedIndex];
            var gender = selectedOption.getAttribute('data-gender');

            if (gender === 'ወንድ') {
                document.getElementById('male').checked = true;
            } else if (gender === 'ሴት') {
                document.getElementById('female').checked = true;
            }
        });
    </script>
    <div>
        <footer>
            Develope by Habtagiorgis Yilkal (ICT)</br>
            habtayilekal10@gmail.com
        </footer>
    </div>
</body>

</html>