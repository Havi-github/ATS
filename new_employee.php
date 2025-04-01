<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

include 'db.php';

$pdo->exec("SET NAMES 'utf8mb4'");

// Handle form submission for adding attendance records
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $name = $_POST['name'];
    $department = $_POST['department'];
    $gender = $_POST['gender'];
    $bank_account = $_POST['bank_account'];

    // Prepare the SQL statement to insert employee data into the employees table
    $query = $pdo->prepare("INSERT INTO employees (name, department, gender, bank_account) VALUES (?, ?, ?, ?)");

    // Execute the query with the form data
    $result = $query->execute([$name, $department, $gender, $bank_account]);

    // Set success message
    $successMessage = "አዲስ ስራተኛ በትክክል ተመዝግቧል!!";
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

        nav.navbar {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            /* Gradient background */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Subtle shadow for depth */
        }

        h1 {
            text-align: center;
        }

        /* .form-group {
            width: 40%;
            margin-left: 25%;

        } */
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
        <!-- <a href="index.php" class="navbar-brand">Home</a> -->
        <a href="index.php" class="btn btn-light ml-auto">Back to Records</a>
    </nav>

    <div>
        <img src="logo_left.png" alt="Logo Left" class="logo">
        <h2 class="mb-4">በአዲስ አበባ ከተማ አስተዳደር ምግብና መድሃኒት ባለስልጣን</br>የአዲሰ ባለሙያ መመዝገቢያ</h2>
        <img src="logo_right.png" alt="Logo Right" class="logo">
    </div>

    <div class="container mt-5">
        <form method="POST">
            <div class="form-group">
                <label for="name">የስራተኛው ሰም</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="department">የስራ ክፍል</label>
                <input type="text" name="department" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="gender">ዖታ</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="male" value="ወንድ" required>
                    <label class="form-check-label" for="male">ወንድ</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="female" value="ሴት" required>
                    <label class="form-check-label" for="female">ሴት</label>
                </div>
            </div>

            <div class="form-group">
                <label for="bankAccount">የባንክ ሂሳብ ቁጥር</label>
                <input type="text" name="bank_account" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>
    <footer>
        Develope by Habtagiorgis Yilkal (ICT)</br>
        habtayilekal10@gmail.com
    </footer>

</html>