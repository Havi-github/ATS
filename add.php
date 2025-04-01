<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

include 'db.php';

$pdo->exec("SET NAMES 'utf8mb4'");

// Get the search query from the form, if any
$search = $_GET['search'] ?? '';

// Modify the SQL query to filter employees by name if a search query is provided
$query = $pdo->prepare("SELECT id, name, bank_account, gender FROM employees WHERE name LIKE ?");
$query->execute(['%' . $search . '%']);
$employees = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch payment types for the dropdown
$payment_types = $pdo->query("SELECT * FROM payment_types")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding attendance records
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $bank_account = $_POST['bank_account'];
    $payment_type_id = $_POST['payment_type_id'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $reason = $_POST['reason'];
    $gender = $_POST['gender'];

    // Convert dates to DateTime objects for processing
    $start = new DateTime($from_date);
    $end = new DateTime($to_date);

    // Insert attendance records for each day in the range
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

    foreach ($dateRange as $date) {
        $formatted_date = $date->format('Y-m-d');

        // Check for duplicate entries
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->execute([$employee_id, $formatted_date]);
        $recordExists = $stmt->fetchColumn();

        if (!$recordExists) {
            // Insert new attendance record  Check this section
            $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, bank_account, payment_type_id, date, reason, gender) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $bank_account, $payment_type_id, $formatted_date, $reason, $gender]);
        }
    }

    // Set success message
    $successMessage = "አቴንዳስ በትክክል ተመዝግቧል!!";
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
    <script>
        // JavaScript for closing the popup
        function closePopup() {
            document.getElementById("popup").style.display = "none";
        }
    </script>
    <style>
        /* Style for the search form */
        h2 {
            text-align: center;
        }

        .logo {
            width: 80px;
            /* Default size for small screens */
            height: 80px;
            /* Ensure equal width and height for perfect circle */
            position: absolute;
            border-radius: 50%;
            /* Makes the logo circular */
            object-fit: cover;
            /* Ensures the image fits nicely within the circle */
            overflow: hidden;
            /* Ensures any part of the image outside the circle is hidden */
        }

        .logo:first-child {
            left: 10%;
        }

        .logo:last-child {
            right: 10%;
            top: 15%;
        }

        @media (min-width: 768px) {
            .logo {
                width: 100px;
                /* Larger size for medium screens */
                height: 100px;
                /* Equal height for a circular shape */
            }
        }

        @media (min-width: 1200px) {
            .logo {
                width: 120px;
                /* Even larger size for large screens */
                height: 120px;
                /* Equal height for a circular shape */
            }
        }
        
        .logo {
            width: 100px;
            height: auto;
            position: absolute;
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
            margin-left: 45%;
        }


        .search-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            width: 250px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        .search-bar button {
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .search-bar button:hover {
            background-color: #0056b3;
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

<?php if ($successMessage): ?>
    <div class="popup success-message" id="popup">
        <span class="close" onclick="document.querySelector('.success-message').style.display='none'">&times;</span>
        <p><?php echo $successMessage; ?></p>
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('popup').style.display = 'none';
        }, 5000); // Automatically close after 5 seconds
    </script>
<?php endif; ?>


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <!-- <a href="index.php" class="navbar-brand" href="#">Home</a> -->
        <a href="index.php" class="btn btn-light ml-auto">Back to Records</a>
    </nav>
    <div>
        <img src="logo_left.png" alt="Logo Left" class="logo">
        <h2 class="mb-4">በአዲስ አበባ ከተማ አስተዳደር ምግብና መድሃኒት ባለስልጣን</br>የአዲሰ ተሳታፊ አበል መመዝገቢያ</h2>
        <img src="logo_right.png" alt="Logo Right" class="logo">
    </div>

    <div class="container mt-5">
        <!-- Search bar form to filter employees -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by employee name...">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!--Add hidden input field to hold the employee ID After Search-->
        <input type="hidden" id="employeeIdInput" name="employee_id" value="">

        <form method="POST">
            <div class="form-group">
                <label for="employee">የስራተኛ ስም</label>
                <select name="employee_id" id="employeeSelect" class="form-control" required onchange="fetchBankAccount(this.value)" required>
                    <?php if (count($employees) > 0): ?>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee['id'] ?>" <?= isset($_POST['employee_id']) && $_POST['employee_id'] == $employee['id'] ? 'selected' : '' ?> data-gender="<?= $employee['gender'] ?>">
                                <?= htmlspecialchars($employee['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No employees found</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <lable for="bankAccount">ባንክ አካውንት</lable>
                <input type="text" id="bankAccount" name="bank_account" class="form-control" value="<?= isset($_POST['bank_account']) ? htmlspecialchars($_POST['bank_account']) : '' ?>" readonly>

            </div>

            <div class="form-group">
                <label for="gender">ዖታ</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="male" value="ወንድ"
                        <?php if (isset($employee['gender']) && $employee['gender'] == 'ወንድ') echo 'checked'; ?> required>
                    <label class="form-check-label" for="male">ወንድ</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="female" value="ሴት"
                        <?php if (isset($employee['gender']) && $employee['gender'] == 'ሴት') echo 'checked'; ?> required>
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
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fetch the bank account for the first employee automatically
        $(document).ready(function() {
            var firstEmployeeId = $('#employeeSelect option:first').val();
            fetchBankAccount(firstEmployeeId); // Automatically fetch the bank account for the first employee
        });

        document.getElementById('employeeSelect').addEventListener('change', function() {
            var employeeId = this.value;
            console.log("Selected Employee ID: " + employeeId); // Log selected employee ID
            fetchBankAccount(employeeId);
        });

        function fetchBankAccount(employeeId) {
            if (employeeId) {
                $.ajax({
                    url: 'fetch_bank_account.php',
                    method: 'POST',
                    data: {
                        employee_id: employeeId
                    },
                    success: function(response) {
                        console.log(response); // Log the response
                        document.getElementById('bankAccount').value = response;
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            } else {
                document.getElementById('bankAccount').value = '';
            }
        }
    </script>

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