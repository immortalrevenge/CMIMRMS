<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
  echo '<script>window.location.href="index";alert("Please Login");</script>';
  exit();
}
require_once('tcpdf/library/tcpdf.php');
require_once __DIR__ . '/pdf/vendor/autoload.php';
include 'conn.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["print"])) {
  // Sanitize the input to prevent SQL injection
  $startDate = $_POST["start_date"] . " 00:00:00";
  $endDate = $_POST["end_date"] . " 23:59:59";

  // SQL query to get data for the selected month
  $sql = "SELECT m.*, i.*
          FROM medicineinfo_tbl m
          JOIN inventory_tbl i ON m.med_id = i.med_id WHERE 
              i.date_created = (
                  SELECT 
                      MAX(date_created) 
                  FROM 
                      inventory_tbl WHERE 
                    date_created BETWEEN '$startDate' AND '$endDate' AND med_id = i.med_id)
          ORDER BY 
              m.med_name ASC;";
  
  $query_run = mysqli_query($conn, $sql);

  // Create a new PDF instance using mPDF
  $mpdf = new \Mpdf\Mpdf();

  // Set document properties
  $mpdf->SetTitle('Medicine Inventory');
  $mpdf->SetAuthor('Your Name');

  // Create HTML content for the table
  $html = '
   <div>
      <h5>Republic of the Philippines</h5>
      <h1>NUEVA ECIJA UNIVERSITY OF SCIENCE AND TECHNOLOGY</h1>
      <br>
      <h2>Medicine Inventory</h2>
      <br>
  </div>
<table border="1" cellpadding="5">
  <thead>
      <tr>
          <th><b>ID no.</b></th>
          <th><b>Medicine Name</b></th>
          <th><b>Category</b></th>
          <th><b>Type</b></th>
          <th><b>Dosage</b></th>
          <th><b>Quantity in Stock</b></th>
          <th><b>Date</b></th>
      </tr>
  </thead>
  <tbody>';
  
  if($query_run) {
      while ($row = $query_run->fetch_assoc()) {
          $html .= "<tr>";
          $html .= "<td data-lable='ID no.'>" . $row['med_id'] . "</td>";
          $html .= "<td data-lable='Medicine'>" . $row['med_name'] . "</td>";
          $html .= "<td data-lable='Category'>" . $row['category'] . "</td>";
          $html .= "<td data-lable='Type'>" . $row['type'] . "</td>";
          $html .= "<td data-lable='Dosage'>" . $row['dosage'] . "</td>";
          $html .= "<td data-lable='Stock'>" . $row['stock'] . "</td>";
          $html .= "<td data-lable='Date'>" . $row['date_created'] . "</td>";
          $html .= "</tr>";
      }
  } else {
      $html .= "<tr><td colspan='7'>No records found for this month.</td></tr>";
  }
  
  $html .= '
  </tbody>
</table>';

  // Write HTML to PDF using mPDF
  $mpdf->WriteHTML($html);

  // Output the PDF to the browser
  $mpdf->Output('medicine_inventory ' . $startDate . ' to ' . $endDate . '.pdf', 'I');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["printcases"])) {
 // SQL query to get health cases categorized by month and case type
$sql = "WITH months AS (
  SELECT 1 AS month_num, 'January' AS month_name UNION ALL
  SELECT 2, 'February' UNION ALL
  SELECT 3, 'March' UNION ALL
  SELECT 4, 'April' UNION ALL
  SELECT 5, 'May' UNION ALL
  SELECT 6, 'June' UNION ALL
  SELECT 7, 'July' UNION ALL
  SELECT 8, 'August' UNION ALL
  SELECT 9, 'September' UNION ALL
  SELECT 10, 'October' UNION ALL
  SELECT 11, 'November' UNION ALL
  SELECT 12, 'December'
)
SELECT 
  m.month_name,
  c.case_name,
  IFNULL(COUNT(hc.prescription_id), 0) AS total_cases
FROM 
  months m
CROSS JOIN 
  cases_tbl c
LEFT JOIN 
  prescription_tbl hc ON MONTH(hc.dOprescription) = m.month_num 
  AND YEAR(hc.dOprescription) = 2024 
  AND hc.complaint = c.case_name
GROUP BY 
  m.month_name, c.case_name
ORDER BY 
  m.month_num, c.case_id;";

// Execute the query
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
  die("SQL Error: " . $conn->error);
}

// Prepare to structure the report
$reportData = [];
$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$totalCases = array_fill(0, 12, 0); // Initialize total cases for each month

// Process the results
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $caseName = $row['case_name'];
      $monthName = $row['month_name'];
      $total = $row['total_cases'];

      // Populate report data with total cases under each case type and month
      $reportData[$caseName][$monthName] = $total;

      // Find month index for total case calculation
      $monthIndex = array_search($monthName, $months);
      if ($monthIndex !== false) {
          $totalCases[$monthIndex] += $total; // Increment total cases for the month
      }
  }
} else {
  echo "No records found.";
}

// Close the connection
$conn->close();

// Start output buffering
ob_start();

// Generate the HTML content for the PDF
echo "<h1>Health Cases Report by Month</h1>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Case Type</th>";

// Print month headers
foreach ($months as $month) {
  echo "<th>$month</th>";
}
echo "</tr>";

// Print case types and their corresponding cases
foreach ($reportData as $caseType => $cases) {
  echo "<tr><td>$caseType</td>";
  foreach ($months as $month) {
      // Display the number of cases or 0 if not present
      $count = isset($cases[$month]) ? $cases[$month] : 0;
      echo "<td>$count</td>";
  }
  echo "</tr>";
}

// Add total cases row
echo "<tr><td><strong>Total Cases</strong></td>";
foreach ($totalCases as $total) {
  echo "<td>$total</td>";
}
echo "</tr>";

echo "</table>";

// Get the HTML content
$htmlContent = ob_get_clean();

// Create an instance of the mPDF class
$mpdf = new \Mpdf\Mpdf();

// Write the HTML content to the PDF
$mpdf->WriteHTML($htmlContent);

// Output the PDF to the browser
$mpdf->Output('health_cases_report.pdf', 'D'); // 'D' to download the file

exit; // Ensure the script stops here after generating the PDF
}
try {
  // Default SQL query to retrieve data for the current month
  $default_sql = "SELECT dOprescription, complaint AS cases, COUNT(*) AS no_cases 
                  FROM prescription_tbl  
                  WHERE MONTH(dOprescription) = MONTH(NOW())
                  GROUP BY complaint";
  $result = $conn->query($default_sql);

  // Variables to store the data for charting
  $Mno_cases = [];
  $Mcases = [];

  // Check if the filter form is submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["filter_dates"])) {
      $startDate = $_POST["start_date"];
      $endDate = $_POST["end_date"];

      // Ensure dates are valid and sanitize the inputs
      if (!empty($startDate) && !empty($endDate)) {
          // Use prepared statement for filtering
          $sql = "SELECT dOprescription, complaint AS cases, COUNT(*) AS no_cases 
                  FROM prescription_tbl  
                  WHERE dOprescription BETWEEN ? AND ?
                  GROUP BY complaint";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("ss", $startDate, $endDate);
          $stmt->execute();
          $result = $stmt->get_result();

          // Fetch the filtered data
          while ($row = $result->fetch_assoc()) {
              $Mcases[] = $row['cases'];
              $Mno_cases[] = $row['no_cases'];
          }
      } else {
          throw new Exception("Invalid date range provided.");
      }
  } else {
      // Default case: retrieve data for the current month
      while ($row = $result->fetch_assoc()) {
          $Mcases[] = $row['cases'];
          $Mno_cases[] = $row['no_cases'];
      }
  }

} catch (Exception $e) {
  $error = $e->getMessage();
  echo "Error: " . $error;
}
try {
  $sql = "SELECT m.med_name, i.stock, i.date_created
  FROM medicineinfo_tbl m
  JOIN inventory_tbl i ON m.med_id = i.med_id WHERE 
      i.date_created = (
          SELECT 
              MAX(date_created) 
          FROM 
              inventory_tbl WHERE 
              med_id = i.med_id);";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $result = $stmt->get_result();
  
  
  $stock = [];
  $medicine = [];
  $dates = [];
  
  while ($row = $result->fetch_assoc()) {
      $medicine[] = $row['med_name'];
      $stock[] = $row['stock'];
      $dates[] = $row['date_created'];
  }

  // Check if the filter form is submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["filterstock"])) {
      $startDate = $_POST["start_date"] . " 00:00:00";
      $endDate = $_POST["end_date"] . " 23:59:59";

      // Ensure dates are valid and sanitize the inputs
      if (!empty($startDate) && !empty($endDate)) {
          // Use prepared statement for filtering
            $sql = "SELECT m.med_name, i.stock, i.date_created
            FROM medicineinfo_tbl m
            JOIN inventory_tbl i ON m.med_id = i.med_id WHERE 
                i.date_created = (
                    SELECT 
                        MAX(date_created) 
                    FROM 
                        inventory_tbl WHERE 
                      date_created BETWEEN '$startDate' AND '$endDate' AND med_id = i.med_id);";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$stock = [];
$medicine = [];
$dates = [];

while ($row = $result->fetch_assoc()) {
    $medicine[] = $row['med_name'];
    $stock[] = $row['stock'];
    $dates[] = $row['date_created'];
}
      } else {
          throw new Exception("Invalid date range provided.");
      }
  } else {
      // Default case: retrieve data for the current month
      while ($row = $result->fetch_assoc()) {
          $Mcases[] = $row['cases'];
          $Mno_cases[] = $row['no_cases'];
      }
  }

} catch (Exception $e) {
  $error = $e->getMessage();
  echo "Error: " . $error;
}
try {
  // Step 1: Query to find the case (complaint) with the highest count
$max_case_query = "
SELECT complaint, COUNT(*) AS case_count 
FROM prescription_tbl 
GROUP BY complaint 
ORDER BY case_count DESC 
LIMIT 1";  // Get the complaint with the most cases
$max_case_result = $conn->query($max_case_query);
$max_case_row = $max_case_result->fetch_assoc();
$highest_case = $max_case_row['complaint'];  // This is the complaint with the highest count

// Step 2: Retrieve data for the highest case, grouped by gender
$sql = "
SELECT 
  pi.gender,
  COUNT(*) AS gender_count
FROM 
  prescription_tbl p
JOIN 
  personalinfo_tbl pi ON p.unique_id = pi.unique_id
WHERE 
  p.complaint = ? 
GROUP BY 
  pi.gender";  // Get the count of male and female for this specific case

// Prepare the statement to avoid SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $highest_case);  // Bind the most frequent case
$stmt->execute();
$result = $stmt->get_result();

// Variables for chart data
$gender_labels = [];
$gender_counts = ['Male' => 0, 'Female' => 0];


  // Check if the filter form is submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["case"])) {
      $case = $_POST["case"];

      // Ensure dates are valid and sanitize the inputs
      if (!empty($case)) {
          // Use prepared statement for filtering
          $sql = "SELECT 
  pi.gender,
  COUNT(*) AS gender_count
FROM 
  prescription_tbl p
JOIN 
  personalinfo_tbl pi ON p.unique_id = pi.unique_id
WHERE 
  p.complaint = ? 
GROUP BY 
  pi.gender";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("s", $case);
          $stmt->execute();
          $result = $stmt->get_result();

          // Fetch the filtered data
          while ($row = $result->fetch_assoc()) {
              // Count gender distribution
              if ($row['gender'] == 'Male') {
                  $gender_counts['Male'] += $row['gender_count'];
              } elseif ($row['gender'] == 'Female') {
                  $gender_counts['Female'] += $row['gender_count'];
              } // Number of cases for each gender
              }
      } else {
          throw new Exception("Invalid date range provided.");
      }
  } else {
      
while ($row = $result->fetch_assoc()) {
  // Count gender distribution
  if ($row['gender'] == 'Male') {
      $gender_counts['Male'] += $row['gender_count'];
  } elseif ($row['gender'] == 'Female') {
      $gender_counts['Female'] += $row['gender_count'];
  } // Number of cases for each gender
  }
  }

} catch (Exception $e) {
  $error = $e->getMessage();
  echo "Error: " . $error;
}
// Age
try {
  // Step 1: Query to find the case (complaint) with the highest count
$max_case_query = "
SELECT complaint, COUNT(*) AS case_count 
FROM prescription_tbl 
GROUP BY complaint 
ORDER BY case_count DESC 
LIMIT 1";  // Get the complaint with the most cases
$max_case_result = $conn->query($max_case_query);
$max_case_row = $max_case_result->fetch_assoc();
$highest_case = $max_case_row['complaint'];  // This is the complaint with the highest count

// Step 2: Retrieve data for the highest case, grouped by gender
$sql = "
SELECT 
  pi.age,
  COUNT(*) AS no_age
FROM 
  prescription_tbl p
JOIN 
  personalinfo_tbl pi ON p.unique_id = pi.unique_id
WHERE 
  p.complaint = ? 
GROUP BY 
  pi.age";  // Get the count of male and female for this specific case

// Prepare the statement to avoid SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $highest_case);  // Bind the most frequent case
$stmt->execute();
$result = $stmt->get_result();

// Variables for chart data
$age = [];
$age_counts = [];


  // Check if the filter form is submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["case"])) {
      $case = $_POST["case"];

      // Ensure dates are valid and sanitize the inputs
      if (!empty($case)) {
          // Use prepared statement for filtering
          $sql = "SELECT 
  pi.age,
  COUNT(*) AS no_age
FROM 
  prescription_tbl p
JOIN 
  personalinfo_tbl pi ON p.unique_id = pi.unique_id
WHERE 
  p.complaint = ? 
GROUP BY 
  pi.age";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("s", $case);
          $stmt->execute();
          $result = $stmt->get_result();

          // Fetch the filtered data
          while ($row = $result->fetch_assoc()) {
              $age [] = $row['age'];
              $age_counts[] = $row['no_age'];
          }
      } else {
          throw new Exception("Invalid date range provided.");
      }
  } else {
      
  // Count gender distribution
  while ($row = $result->fetch_assoc()) {
      $age [] = $row['age'];
      $age_counts[] = $row['no_age'];
  }
  }

} catch (Exception $e) {
  $error = $e->getMessage();
  echo "Error: " . $error;
}
// Course
try {
  // Step 1: Query to find the case (complaint) with the highest count
$max_case_query = "
SELECT complaint, COUNT(*) AS case_count 
FROM prescription_tbl 
GROUP BY complaint 
ORDER BY case_count DESC 
LIMIT 1";  // Get the complaint with the most cases
$max_case_result = $conn->query($max_case_query);
$max_case_row = $max_case_result->fetch_assoc();
$highest_case = $max_case_row['complaint'];  // This is the complaint with the highest count

// Step 2: Retrieve data for the highest case, grouped by gender
$sql = "
SELECT 
  pi.course,
  COUNT(*) AS no_course
FROM 
  prescription_tbl p
JOIN 
  personalinfo_tbl pi ON p.unique_id = pi.unique_id
WHERE 
  p.complaint = ? 
GROUP BY 
  pi.course";  // Get the count of male and female for this specific case

// Prepare the statement to avoid SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $highest_case);  // Bind the most frequent case
$stmt->execute();
$result = $stmt->get_result();

// Variables for chart data
$course = [];
$course_counts = [];


  // Check if the filter form is submitted
  // Check if the filter form is submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["case"])) {
      $case = $_POST["case"];

      // Ensure dates are valid and sanitize the inputs
      if (!empty($case)) {
          // Use prepared statement for filtering
          $sql = "SELECT 
  pi.course,
  COUNT(*) AS no_course
FROM 
  prescription_tbl p
JOIN 
  personalinfo_tbl pi ON p.unique_id = pi.unique_id
WHERE 
  p.complaint = ? 
GROUP BY 
  pi.course";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("s", $case);
          $stmt->execute();
          $result = $stmt->get_result();

          // Fetch the filtered data
          while ($row = $result->fetch_assoc()) {
              $course [] = $row['course'];
              $course_counts[] = $row['no_course'];
          }
      } else {
          throw new Exception("Invalid date range provided.");
      }
  } else {
      
  // Count gender distribution
  while ($row = $result->fetch_assoc()) {
      $course [] = $row['course'];
      $course_counts[] = $row['no_course'];
  }
  }

} catch (Exception $e) {
  $error = $e->getMessage();
  echo "Error: " . $error;
}
?>

<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="userstyle.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
  <!-- Font Awesome Cdn Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
</head>
<body>
  <div class="container">
  <nav>
      <ul>
      <li><div class="logo">
        <img src="images/clinicLogo.png" alt="neust logo">
          <span class="nav-header">Clinic</span>
        </div></li>
        <li><a href="Administrator">
          <i class="fas fa-home"></i>
          <span class="nav-item">Dashboard</span>
        </a></li>
        <li><a href="Patient Record">
          <i class="fas fa-notes-medical"></i>
          <span class="nav-item">Medical Record</span>
        </a></li>
        <li><a href="Medicine Inventory">
          <i class="fas fa-medkit"></i>
          <span class="nav-item">Medicine Inventory</span>
        </a></li>
        <li><a href="Clinic Report">
        <i  id="active" class="fas fa-chart-line"></i>
          <span  id="active" class="nav-item">Report</span>
        </a></li>
        <li><a href="Account">
         <i class="fas fa-users-cog"></i>
          <span class="nav-item">Account</span>
        </a></li>
        <li><a href="Archive">
          <i class="fas fa-archive"></i>
          <span class="nav-item">Archive</span>
        </a></li>
      
      </ul>
      <a href="logout" class="logout">
          <i class="fas fa-sign-out-alt"></i>
          <span class="nav-item">Log out</span>
        </a>
  </nav>
    <section class="main">
    <div class="main--content">
       

        <div class="tableR">
        <div class="chartR">
        <div>
            <h2>Case Distribution <span id="selected-month"></span></h2>
            <div class="filter-container">
            <form class="filter" action="" method="post">
                <label class="labelR" for="start_date">Start Date:</label>
                <input class="fltrdate" type="date" name="start_date" required>
                <label class="labelR" for="end_date">End Date:</label>
                <input class="fltrdate" type="date" name="end_date" required> 
                <button class="filterbtn" type="submit" name="filter_dates">Apply Filter</button>
            </form>
            
            <form class="filter"  method="post">
                <button type='submit' class='pdf-btn2' name='printcases'><i class="fas fa-print"></i></button>
            </form>
            </div>

              <canvas id="WcasesChart"></canvas>
              <div>        
    </div>
 
</div>
<script>
    var ctx = document.getElementById('WcasesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar', // You can change this to 'bar', 'pie', etc.
        data: {
            labels: <?php echo json_encode($Mcases); ?>, // Days of the month
            datasets: [{
                label: [],
                data: <?php echo json_encode($Mno_cases); ?>,
                backgroundColor: '#fdcb0e',
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,  // Show legend
                    labels: {
                        font: {
                            size: 14,  // Font size for legend
                        }
                    }
                }
            }
        }
    });

    // Add event listener to the dropdown menu
</script>

          </div>
          <div class="chartR">
          <h2>Stock in Medicine Cabinet</h2>
          <div class="filter-container">
              <form class="filter" action="" method="post">
  <label class="labelR" for="start_date">Start Date:</label>
  <input class="fltrdate" type="date" name="start_date" required>
  
  <label class="labelR" for="end_date">End Date:</label>
  <input class="fltrdate" type="date" name="end_date" required> 

  <button class="filterbtn" type="submit" name="filterstock">Apply Filter</button>
  
  <button type="submit" class="pdf-btn2" name="print"><i class="fas fa-print"></i></button>
</form>
</div>      
  <canvas id="stockChart" style="display: block;font-weight: 600; box-sizing: border-box; height: 282px; width: 564px;" width="705" height="352"></canvas>
  
    </div>
    <script>
      var ctx = document.getElementById('stockChart').getContext('2d');
var salesChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($medicine); ?>,
    datasets: [{
      label: [],
      data: <?php echo json_encode($stock); ?>,
      backgroundColor: '#fdcb0e',
      fill: false
    }]
  },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,  // Show legend
                    labels: {
                        font: {
                            size: 14,  // Font size for legend
                        }
                    }
                }
            }
        }
});
    </script>
       </div>
       <div class="tableR">
       <div class="chartR">
       <h2> Gender Demographic of Case <?php echo empty($case) ? $highest_case : $case; ?></h2>
       
  <form class="filter" action="" method="post">
    
    <select id="caseFilter" name="case" onchange="this.form.submit()">
        <option value="">-- Select a Case --</option>
        <?php
        // Fetch distinct cases to populate the filter
        $case_query = "SELECT DISTINCT complaint FROM prescription_tbl";
        $case_result = $conn->query($case_query);
        
        while ($case_row = $case_result->fetch_assoc()) {
            // Check if the case is selected or not
            $selected = (isset($_POST['case']) && $_POST['case'] == $case_row['complaint']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($case_row['complaint']) . '" ' . $selected . '>' . htmlspecialchars($case_row['complaint']) . '</option>';
        }
        ?>
    </select>
</form>
            <!-- Canvas element for the gender distribution chart -->
            <canvas id="genderChart" style="display: block; box-sizing: border-box; height: 282px; width: 564px;" width="705" height="352"></canvas>
    <script>
    // Chart.js setup for Gender Distribution for the most frequent case
    var ctxGender = document.getElementById('genderChart').getContext('2d');
    var genderChart = new Chart(ctxGender, {
        type: 'bar',  // Chart type
        label: [],
        data: {
            labels: ['Male', 'Female'],
        datasets: [{
            data: [
                <?php echo json_encode($gender_counts['Male']); ?>,
                <?php echo json_encode($gender_counts['Female']); ?>
            ],
            backgroundColor: ['rgba(43, 125, 255, 1)', 'rgba(255, 155, 207, 1)'],  // Colors for Male and Female
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,  // Show legend
                    labels: {
                        font: {
                            size: 14,  // Font size for legend
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,  // Start the y-axis at 0
                    title: {
                        display: true,
                        text: 'Number of Patients'  // Title for y-axis
                    }
                },
                x: {
                    title: {
                        display: true,
                    }
                }
            }
        }
    });
    </script>

</div>
<div class="chartR">
<h2> Age Demographic of Case <?php echo empty($case) ? $highest_case : $case; ?></h2>
<br>
<br>
    
<!-- Canvas element for the gender distribution chart -->
    <canvas id="ageChart"></canvas>

    <script>
    // Chart.js setup for Course Distribution for the most frequent case
    var ctxAge = document.getElementById('ageChart').getContext('2d');
    var ageChart = new Chart(ctxAge, {
        type: 'bar',  // Chart type

        data: {
            labels: <?php echo json_encode($age); ?>,
        datasets: [{
            data: 
                <?php echo json_encode($age_counts); ?>
            ,  // Data corresponding to the courses
            backgroundColor: '#ff6162',
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,  // Show legend
                    labels: {
                        font: {
                            size: 14,  // Font size for legend
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,  // Start the y-axis at 0
                    title: {
                        display: true,
                        text: 'Number of Patients'  // Title for y-axis
                    }
                },
                x: {
                    title: {
                        display: true,
                    }
                }
            }
        }
    });
</script>
</div>
<div class="chartR">
<h2> Course Demographic of Case <?php echo empty($case) ? $highest_case : $case; ?></h2>
<br>
<br>
<canvas id="courseChart"></canvas>
<script>
    // Chart.js setup for Course Distribution for the most frequent case
    var ctxCourse = document.getElementById('courseChart').getContext('2d');
    var courseChart = new Chart(ctxCourse, {
        type: 'bar',  // Chart type
        data: {
            labels: <?php echo json_encode($course); ?>,  // Course labels as X-axis
            datasets: [{
                label: 'Number of Patients per Course',  // Label for the legend
                data: <?php echo json_encode($course_counts); ?>,  // Data corresponding to the courses
                backgroundColor: 'purple',  // Bar color
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,  // Show legend
                    labels: {
                        font: {
                            size: 14,  // Font size for legend
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,  // Start the y-axis at 0
                    title: {
                        display: true,
                        text: 'Number of Patients'  // Title for y-axis
                    }
                },
                x: {
                    title: {
                        display: true,
                    }
                }
            }
        }
    });
</script>
</div>
       </div>
       <div class="tableR">
        

       </div>
       <div class="frequentlyP">
       <h2>Frequently Prescribed</h2>
              <div class="table-scroll">
              <div class="view-table" >
                    <?php

                $query = "SELECT prescription_tbl.prescription_id,prescription_tbl.dOprescription, prescription_tbl.complaint,prescription_tbl.dOprescription,personalinfo_tbl.id_no, medicineinfo_tbl.med_name
                        FROM prescription_tbl JOIN medicineinfo_tbl ON prescription_tbl.med_id = medicineinfo_tbl.med_id
                        JOIN personalinfo_tbl ON prescription_tbl.unique_id = personalinfo_tbl.unique_id ORDER BY prescription_tbl.dOprescription DESC LIMIT 4 ";
                $query_run = mysqli_query($conn, $query);
            ?>
                   <table id="datatableid" class="medicine-table">
                        <thead >
                                <th scope="col" style ="text-align:center;"> ID</th>
                                <th scope="col" style ="text-align:center;">Medicine Name</th>
                                <th scope="col" style ="text-align:center;">Complaint</th>
                                <th scope="col" style ="text-align:center;"> Date </th>
                            </tr>
                        </thead>
                        <?php
                if($query_run)
                {
                    foreach($query_run as $row)
                    {
            ?>
                        <tbody>
                            <tr>
                                <td style ="text-align:center;"> <?php echo $row['id_no']; ?> </td>
                                <td style ="text-align:center;"> <?php echo $row['med_name']; ?> </td>
                                <td style ="text-align:center;"> <?php echo $row['complaint']; ?> </td>
                                <td style ="text-align:center;"> <?php echo $row['dOprescription']; ?> </td>
                            </tr>
                        </tbody>
                        <?php           
                    }
                }
                else 
                {
                  echo'<div style="color: RED; text-align:center; position: relative; top: 150px; font-weight: 700">No users found.</div>';
                }
            ?>
                    </table>
            </div>
            </div>
        </div>
    </section>
    </div>  
</body>
</html>