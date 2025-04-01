<?php
include 'db.php';

$pdo->exec("SET NAMES 'utf8mb4'");

// Get the search query from the form, if any
$search_from = $_GET['search_from'] ?? '';
$search_to = $_GET['search_to'] ?? '';
$search_name = $_GET['search_name'] ?? '';
$search_reason = $_GET['search_reason'] ?? '';

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
");

$query->execute(['%' . $search_name . '%', '%' . $search_reason . '%']);
$records = $query->fetchAll(PDO::FETCH_ASSOC);

// Check if the dates are passed via POST method
if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
} else {
    $from_date = "No start date selected.";
    $to_date = "No end date selected.";
}

// Handle Excel download
if (isset($_POST['download_excel'])) {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="attendance_report.xls"');
    header('Cache-Control: max-age=0');
    
    // Output BOM (Byte Order Mark) for proper encoding in Excel
    echo "\xEF\xBB\xBF";  // UTF-8 BOM

    // Output the table headers with proper encoding and tab separation
    echo mb_convert_encoding("ተ.ቁ\tየስልጣኞች ስም ዝርዝር\tየአካውንት ቁጥር\tጠቅላላ ክፍያ\n", 'UTF-8', 'UTF-8');

    // Output the table data with tab separation for columns and proper encoding
    $no = 1;
    foreach ($records as $record) {
        echo mb_convert_encoding(
            $no++ . "\t" . htmlspecialchars($record['name']) . "\t" . htmlspecialchars($record['bank_account']) . "\t" . htmlspecialchars($record['total_payment']) . " ETB\n",
            'UTF-8', 
            'UTF-8'
        );
    }
    exit;  // Ensure no further output is sent
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>በአዲስ አበባ ከተማ አስተዳደር ምግብና መድሃኒት ባለስልጣን</title>
    <link rel="stylesheet" href="style.css">
    <style>
        h3 {
            font-size: 14px;
            text-align: center;
        }

        .no-border-table {
            border: none;
            width: 100%;
            margin-top: 20px;
        }

        .no-border-table th,
        .no-border-table td {
            border: none;
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="containers mt-5">
        <h3>በአዲስ አበባ ከተማ አስተዳደር ምግብና መድሃኒት ባለስልጣን</h3>
        <h3> <?= htmlspecialchars($search_reason) ?> </h3>
        <h3>ከ____/____/____ እስከ ____/____/____ </h3>

        <!-- Excel Download Button
        <form method="post">
            <button type="submit" name="download_excel" class="btn btn-primary">Download as Excel</button>
        </form> -->

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ተ.ቁ</th>
                    <th>የስልጣኞች ስም ዝርዝር</th>
                    <th>የአካውንት ቁጥር</th>
                    <th>ጠቅላላ ክፍያ</th>
                    <th>ስም እና ፊርማ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $totalPayment = 0;
                foreach ($records as $record):
                    $totalPayment += $record['total_payment'];
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($record['name']) ?></td>
                        <td><?= htmlspecialchars($record['bank_account']) ?></td>
                        <td><?= htmlspecialchars($record['total_payment']) ?> ETB</td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <!-- Total payment row will always show at the end -->
            <tfoot>
                <tr class="total-payment">
                    <td colspan="3" style="text-align: right;"><strong>ጠቅላላ ክፍያ:</strong></td>
                    <td><strong><?= htmlspecialchars($totalPayment) ?> ETB</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature table remains unchanged -->
    <table class="no-border-table">
        <tr>
            <td>ያዘጋጅው</td>
            <td>የከፍለው</td>
            <td>ያፀደቀው</td>
        </tr>
        <tr>
            <td>ስም፡ _______________</td>
            <td>ስም፡ _______________</td>
            <td>ስም፡ _______________</td>
        </tr>
        <tr>
            <td>ፊርማ፡ _______________</td>
            <td>ፊርማ፡ _______________</td>
            <td>ፊርማ፡ _______________</td>
        </tr>
        <tr>
            <td>ቀን፡ _______________</td>
            <td>ቀን፡ _______________</td>
            <td>ቀን፡ _______________</td>
        </tr>
    </table>
</body>

</html>
