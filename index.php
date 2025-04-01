<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

include 'db.php';

$pdo->exec("SET NAMES 'utf8mb4'");

// Pagination setup
$records_per_page = 10; // Number of records per page
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get the search query from the form, if any
$search_name = $_GET['search_name'] ?? '';
$search_reason = $_GET['search_reason'] ?? '';

// Count total records for pagination
$total_records_query = $pdo->prepare("
    SELECT COUNT(DISTINCT a.employee_id, a.reason) AS total
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE e.name LIKE ? AND a.reason LIKE ?
");
$total_records_query->execute(['%' . $search_name . '%', '%' . $search_reason . '%']);
$total_records = $total_records_query->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Modify the SQL query to filter by name if a search query is provided
$query = $pdo->prepare("
    SELECT e.name, a.gender, e.department, e.bank_account, p.payment_type, p.default_amount, 
           COUNT(a.date) AS total_present_days, 
           (COUNT(a.date) * p.default_amount) AS total_payment, 
           a.employee_id, p.id AS payment_type_id, a.reason
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    JOIN payment_types p ON a.payment_type_id = p.id
    WHERE e.name LIKE ? AND a.reason LIKE ?
    GROUP BY a.employee_id, p.payment_type, a.reason
    LIMIT $records_per_page OFFSET $offset
");
$query->execute(['%' . $search_name . '%', '%' . $search_reason . '%']);
$records = $query->fetchAll(PDO::FETCH_ASSOC);
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

        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
            transition: background-color 0.3s ease, transform 0.2s ease;
            /* Smooth hover effect */
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: scale(1.05);
            /* Slight enlargement on hover */
        }

        .btn-danger:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
            /* Focus effect */
        }

        .navbar .btn {
            margin-left: 10px;
            color: #f8f9fa;
        }

        .navbar-toggler-icon {
            background-color: #fff;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Home</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="add.php">Record Attendance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="new_employee.php">Add New Employee</a>
                </li>
            </ul>
            <span class="navbar-text">Logged in as <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-danger ml-3">Logout</a>
        </div>
    </nav>

    <div>
        <img src="logo_left.png" alt="Logo Left" class="logo">
        <h2>በአዲስ አበባ ከተማ አስተዳደር ምግብና መድሃኒት ባለስልጣን<br>የአበል አቴንዳስ ሲስተም</h2>
        <img src="logo_right.png" alt="Logo Right" class="logo">
    </div>

    <h2 class="mb-4">የተመዘገቡ አቴንዳሶች</h2>

    <div>
        <!-- Search bar form -->
        <form method="GET" class="form-inline mb-4">
            <input type="text" name="search_name" value="<?= htmlspecialchars($search_name) ?>" class="form-control mr-2" placeholder="Search by name">
            <input type="text" name="search_reason" value="<?= htmlspecialchars($search_reason) ?>" class="form-control mr-2" placeholder="Search by reason">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="print.php?search_name=<?= htmlspecialchars($search_name) ?>&search_reason=<?= htmlspecialchars($search_reason) ?>" class="btn btn-info ml-2">Print Attendance</a>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ስም</th>
                    <th>ዖታ</th>
                    <th>የስራ ክፍል</th>
                    <th>የክፍያ እስኬል</th>
                    <th>የባንከ ቁጥር</th>
                    <th>ጠቅላላ የተገኙበት ቀን</th>
                    <th>ጠቅላላ ክፍያ</th>
                    <th>የአበል ምክንያት</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records): ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['name']) ?></td>
                            <td><?= htmlspecialchars($record['gender']) ?></td>
                            <td><?= htmlspecialchars($record['department']) ?></td>
                            <td><?= htmlspecialchars($record['payment_type']) ?></td>
                            <td><?= htmlspecialchars($record['bank_account']) ?></td>
                            <td><?= htmlspecialchars($record['total_present_days']) ?></td>
                            <td><?= htmlspecialchars($record['total_payment']) ?></td>
                            <td><?= htmlspecialchars($record['reason']) ?></td>
                            <td>
                                <!-- Action buttons for editing and deleting -->
                                <a href="edit.php?employee_id=<?= $record['employee_id'] ?>&payment_type_id=<?= $record['payment_type_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete.php?employee_id=<?= $record['employee_id'] ?>&payment_type_id=<?= $record['payment_type_id'] ?>&reason=<?= urlencode($record['reason']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>

        <!-- Pagination -->
<nav>
    <ul class="pagination justify-content-center">
        <!-- First Page -->
        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?search_name=<?= htmlspecialchars($search_name) ?>&search_reason=<?= htmlspecialchars($search_reason) ?>&page=1"><<</a>
        </li>
        
        <!-- Previous Page -->
        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?search_name=<?= htmlspecialchars($search_name) ?>&search_reason=<?= htmlspecialchars($search_reason) ?>&page=<?= $current_page - 1 ?>"><</a>
        </li>

        <!-- Current Page Display -->
        <li class="page-item active">
            <span class="page-link"><?= $current_page ?></span>
        </li>

        <!-- Next Page -->
        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?search_name=<?= htmlspecialchars($search_name) ?>&search_reason=<?= htmlspecialchars($search_reason) ?>&page=<?= $current_page + 1 ?>">></a>
        </li>

        <!-- Last Page -->
        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?search_name=<?= htmlspecialchars($search_name) ?>&search_reason=<?= htmlspecialchars($search_reason) ?>&page=<?= $total_pages ?>">>></a>
        </li>
    </ul>

    <!-- Dropdown for Quick Page Jump -->
    <div class="text-center mt-2">
        <form method="GET" action="">
            <input type="hidden" name="search_name" value="<?= htmlspecialchars($search_name) ?>">
            <input type="hidden" name="search_reason" value="<?= htmlspecialchars($search_reason) ?>">
            <select name="page" class="form-select d-inline w-auto" onchange="this.form.submit()">
                <option value="" disabled selected>Jump to</option>
                <?php foreach ([10, 20, 30, 50, 100] as $jump_page): ?>
                    <?php if ($jump_page <= $total_pages): ?>
                        <option value="<?= $jump_page ?>" <?= $jump_page == $current_page ? 'selected' : '' ?>><?= $jump_page ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</nav>


        <footer>
            Develope by Habtagiorgis Yilkal (ICT)</br>
            habtayilekal10@gmail.com
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>