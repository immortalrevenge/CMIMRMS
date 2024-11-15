<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo '<script>window.location.href="index";alert("Please Login");</script>';
    exit();
}
include 'conn.php';
require_once __DIR__ . '/pdf/vendor/autoload.php'; // Include mPDF autoloader
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['print'])) {

    // Start output buffering
    ob_start();
    
    $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
    $userId = $_POST['unique_id']; // Get the user ID from form
    
    // Fetch data from database
    $sql = "SELECT * FROM personalinfo_tbl 
            JOIN medicalinfo_tbl ON personalinfo_tbl.unique_id = medicalinfo_tbl.unique_id 
            WHERE personalinfo_tbl.unique_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $userId);  // Use prepared statement to prevent SQL injection
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // HTML content for PDF generation
        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .viewlogo { display: flex; justify-content: flex-end; align-items: center; padding-top: 0; padding-bottom: 10px; text-align: right; width: 100%; }
                #vpicture { width: 100px; height: 100px; border-radius: 50%; }
                .info { width: 90%; font-size: 15px; text-align: left; margin: 10px; padding-left: 10px; }
                .label { font-weight: bold; margin-right: 5px; }
                .info span { color: #333; }
                .personal-info, .health-info { background-color: #f9f9f9; padding: 20px; }
                fieldset { border: 2px solid #ccc; padding: 20px; margin-bottom: 20px; background-color: #f9f9f9; }
                legend { font-weight: bold; font-size: 1.2em; }
                .certifiedBy { text-align: left; }
                .header { width: 100%; }
            </style>
        </head>
        <body>
        <table class="header">
            <tr>
                <td class="image-cell"><img src="images/clinicLogo.png" width="100px" alt="Left Image"></td>
                <td class="text-cell">
                    <h4>Republic of the Philippines</h4>
                    <h3>Nueva Ecija University of Science and Technology</h3>
                    <h2>Medical Record</h2>
                </td>
            </tr>
        </table>

        <div class="viewlogo">
            <img src="' . $row['picture'] . '" id="vpicture" alt="" >
            <span class="nav-header"></span>
        </div>
        <div class="info">
            <span class="label" for="course">Course:</span>
            <span class="label" id="vcourse">'.$row['course'].'</span>
            <span class="label" for="course">SY: 2024-2025</span>
            <span></span>
        </div>

        <div class="grid-container">
    <fieldset class="personal-info">
        <legend>PERSONAL INFORMATION</legend>
        <span class="label" for="name">Name:</span>
        <span id="vlname">'.$row['fname'] ." ".$row['mname']." ". $row['lname'].'</span><br>
        <span class="label" for="religion">Religion:</span>
        <span id="vreligion">'.$row['religion'].'</span><br>
        <span class="label" for="DateOfBirth">Date of Birth:</span>
        <span id="vbday">'.$row['bday'].'</span><br>
        <span class="label" for="age">Age:</span>
        <span id="vage">'.$row['age'].'</span><br>
        <span class="label" for="gender">Gender:</span>
        <span id="vgender">'.$row['gender'].'</span><br>
        <span class="label" for="address">Home Address:</span>
        <span id="vaddress">'.$row['address'].'</span><br>
        <span class="label" for="parent">Parent / Guardian:</span>
        <span id="vguardian">'.$row['guardian'].'</span><br>
        <span class="label" for="contact">Emergency Contact:</span>
        <span id="vemergency">'.$row['emergency_no'].'</span><br>
    </fieldset>

    <fieldset class="health-info">
        <legend>HEALTH INFORMATION</legend>
        <span class="label" for="allergies">Allergies:</span>
        <span id="vallergy">'.$row['allergy'].'</span><br>
        <span class="label" for="asthma">Asthma:</span>
        <span id="vasthma">'.$row['asthma'].'</span><br>
        <span class="label" for="diabetes">Diabetes:</span>
        <span id="vdiabetes">'.$row['diabetes'].'</span><br>
        <span class="label" for="heartDisease">Heart Disease:</span>
        <span id="vheartdisease">'.$row['heartdisease'].'</span><br>
        <span class="label" for="SeizureD">Seizure Disorder:</span>
        <span id="vseizure">'.$row['seizure'].'</span><br>
        <span class="label" for="other">Other Health Condition:</span>
        <span id="vothers">'.$row['others'].'</span><br>
        <span class="label" for="medication">Medication:</span>
        <span id="vmedication">'.$row['medication'].'</span><br>
        <span class="label" for="vaccine">COVID Vaccine:</span>
        <span id="vcovidvax">'.$row['vaccine'].'</span><br>
        <span class="label" for="vaccineStat">Vaccine Status:</span>
        <span id="vcvaxstatus">'.$row['vaccine_status'].'</span><br>
    </fieldset>
 </div>

<fieldset class="pe-info">
    <legend>PHYSICAL EXAMINATION</legend>
    <span class="label" for="exam_date">Date of Examination:</span>
    <span id="vdateOfexamination">'.$row['dOexamination'].'</span><br>
    <span class="label" for="height">Height:</span>
    <span id="vheight">'.$row['height'].'</span><br>
    <span class="label" for="weight">Weight:</span>
    <span id="vweight">'.$row['weight'].'</span><br>
    <span class="label" for="bp">Blood Pressure:</span>
    <span id="vbp">'.$row['bloodpressure'].'</span><br>
    <span class="label" for="blood_type">Blood Type:</span>
    <span id="vbt">'.$row['bloodtype'].'</span><br>
</fieldset>

<fieldset class="life-info">
    <legend>LIFESTYLE INFORMATION</legend>
    <span class="label">Smoking:</span>
    <span id="vsmoking">'.$row['smoking'].'</span><br>
    <span class="label">Liquor Drinking:</span>
    <span id="vliquordrinking">'.$row['liquor'].'</span><br>
</fieldset>

<span class="certifiedBy">
<p>Certified by: <b>';
$user_id = $_SESSION['user_id']; // or from $_GET, $_POST, etc.

$sql = "SELECT * FROM user_tbl WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); 
$stmt->execute();

// Get the result set from the executed query
$result = $stmt->get_result();

// Fetch the single row where user_id matches
$srow = $result->fetch_assoc(); // Fetch as an associative array

// Check if any result is returned
if ($srow) {
    // Access the row data here, e.g.:
    $html .= ' '. $srow['lname'] . ", " . $srow['fname'] . '
    <br><span id="vbt">' . $srow['position'] . '</span><br>';
    // Add other fields here...
} else {
    echo "No record found for the specified user_id.";
}

// Close the statement
$html .= '</b></p>
<br>
<br>
<br>
</span>
        </body>
        </html>
        ';
        
        // Clear any previously echoed output
        ob_end_clean();

        // Create mPDF instance and generate the PDF
        $mpdf->WriteHTML($html);
        $mpdf->Output('medical_record_' . $row['id_no'] . '.pdf', 'I'); // Output inline
    } else {
        echo "No records found.";
    }
}
          function fetchUserById($conn, $userId) {
            $userId = intval($userId); // Sanitize the input to prevent SQL injection
            $sql = "SELECT * FROM personalinfo_tbl 
                JOIN medicalinfo_tbl ON personalinfo_tbl.unique_id = medicalinfo_tbl.unique_id 
                WHERE personalinfo_tbl.unique_id = $userId";
            return $conn->query($sql);
        }
        // Handle AJAX request
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
            $userId = $_GET['id'];
            $result = fetchUserById($conn, $userId);
        
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo json_encode($row);
            } else {
                echo json_encode([]);
            }
            exit; // End the script to prevent additional output
        }
// Handle the AJAX request if `action` and `id` are set in the URL parameters
if (isset($_GET['action']) && $_GET['action'] === 'viewHistory' && isset($_GET['id'])) {
    $historyId = intval($_GET['id']);
    $sql = "SELECT p.*, m.* 
                    FROM prescription_tbl p 
                    JOIN medicineinfo_tbl m ON p.med_id = m.med_id 
                    WHERE p.unique_id = $historyId";
    
    $result = $conn->query($sql);
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode($data); // Return data in JSON format for AJAX
    exit; // Stop further execution after handling the AJAX request
}

                
        
        
?>
<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Medical Record</title>
  <link rel="stylesheet" href="userStyle.css" />
  <script defer src="scriptpop.js"></script>
  
<!-- jsPDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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
        <li><a href="Clinic">
          <i class="fas fa-home"></i>
          <span class="nav-item">Dashboard</span>
        </a></li>
        <li><a href="Medical Record">
          <i id="active" class="fas fa-notes-medical"></i>
          <span  id="active" class="nav-item">Medical Record</span>
        </a></li>
        <li><a href="Inventory">
          <i class="fas fa-medkit"></i>
          <span class="nav-item">Medicine Inventory</span>
        </a></li>
        <li><a href="Report">
        <i class="fas fa-chart-line"></i>
          <span class="nav-item">Report</span>
        </a></li>
        <li><a href="Settings">
        <i class="fas fa-user-cog"></i>
        <span class="nav-item">Account Setting</span>
        </a></li>
        
        
      </ul>
      <a href="logout" class="logout">
          <i class="fas fa-sign-out-alt"></i>
          <span class="nav-item">Log out</span>
        </a>
  </nav>
  <section class="main">
  <div class="main--content">
        <div class="header--wrapper">
          <div class="header--title">
          <h2>Medical Record</h2>
          </div>
          <div class="user--info">
            
            <h4><?php echo $_SESSION['username']?></h4>
          </div>
        </div>
        <section class="tableM">

        <div class="table-list">
        <div class="header-container">
        <div>
        <label for="medicine">Blood Type</label>
          <select class="fill" id="blood" onchange="filterTable()"><br>
          <option value="All">All</option>
          <?php
             // Query to fetch distinct blood types from the database
    $query = "SELECT DISTINCT bloodtype FROM medicalinfo_tbl";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Loop through the result set and output each distinct blood type in an option element
        while ($row = $result->fetch_assoc()) {
            echo "<option>" . htmlspecialchars($row["bloodtype"]) . "</option>";
        }
    } else {
        echo "<option>No blood types available</option>";
    }
            ?>
        </select>
          </div>
        <div class="search--box">
        <form id="searchForm" method="POST" action="">
        <i class="fas fa-search" id="responSearch"></i>
        <input type="text" name="search" placeholder="Search by ID, Name" id="searchInput" list="patient" autocomplete="off" value="<?php echo $search_query; ?>"/>
        <?php
// Fetch data from the medicines table
$sql = "SELECT * FROM personalinfo_tbl";
$result = $conn->query($sql);

// Start outputting the datalist
echo '<datalist id="patient">';

// Check if there are results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['fname']) . ' ' . htmlspecialchars($row['lname']) . '">';
    }
} else {
    echo '<option value="No Patient found">';
}

echo '</datalist>';
        ?>
        </form>

            </div>
      <div class="tooltip">
      <span class="tooltiptext">Register New Record</span>
        <button class="addBtn" data-modal-target='#addform'>
          <i class="fas fa-plus" id="plus"></i><span class="responText">Register</span>
        </button>
      </div>
      <button class="addBtn" data-modal-target='#printform'>
          <i class="fa fa-print" aria-hidden="true" id="plus"></i><span  class="responText">Print</span>
          <i ></i>
        </button>
      
    </div>
        <div class="responsive-tbl">
        <div class="tbl_container">
        <div class="table-scroll">
            <table class="tbl" id="tbl">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Course</th>
                    <th>Year & Section</th>
                    <th>Blood Type</th>
                    <th>Contact No.</th>
                    <th colspan="2">Action</th>
                </tr>
                </thead>  
                <tbody>
                <?php
                  // Display the list of users
                  $result = fetchUsers($conn);
                  if ($result->num_rows > 0) {
  
                    while ($row = $result->fetch_assoc()) {
                        $userId = $row['unique_id'];
                        echo "<tr>";
                        echo "<td data-lable='Name'>" . $row['fname'] . " " . $row['lname'] . "</td>";
                        echo "<td data-lable='Age'>" . $row['age'] . "</td>";
                        echo "<td data-lable='Course'>" . $row['course'] . "</td>";
                        echo "<td data-lable='Year & Section'>" . $row['year_level'] . " " . $row['section'] . "</td>";
                        echo "<td data-lable='Blood Type'>" . $row['bloodtype'] . "</td>";
                        echo "<td data-lable='Contact No.'>" . $row['contact_no'] . "</td>";
                        
                        echo "<td>";
                        echo "<button type='button' class='viewbtn' id='viewmedical' data-modal-target='#viewrecord' data-id='$userId'  data-history-id='$userId'><i class='fas fa-eye'></i>
                              <span class='button-text'>View</span></button>";
                        echo "<button type='button' class='editbtn' data-modal-target='#updateModal' data-id='$userId'><i class='fas fa-edit'></i>
                              <span class='button-text'>Edit</span></button>";
                        echo "</td>";
                    
                        // Corrected the form for the archive button
                        echo "<td>
                                <form id='archiveForm_" . $row['unique_id'] . "' method='post'>
                                    <input type='hidden' name='user_id' value='" . $row['unique_id'] . "'>
                                    <button type='button' class='archivebtn' onclick='confirmArchive(". $row['unique_id'] .")'>
                                        <i class='fas fa-archive'></i>
                                        <span class='button-text'>Archive</span>
                                    </button>
                                    <input type='hidden' name='archive' value='1'>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";     
                    echo "</table>";
                    } else {
                        echo'<div style="color: RED; text-align:center; position: relative; top: 150px; font-weight: 700">No users found.</div>';
                    }
                    
                  ?> 

        </div>
    </div>
    </div>
  </div>
  </section>
  </section>
</div>
<!-- Registration Form -->
<div class="modal" id="addForm">
        <div class="modal-body" >
        <div class="formcontainer2">
            <div class="step-row">
                <div id="progress"></div>
                <div class="step-col"><small>Step 1</small></div>
                <div class="step-col"><small>Step 2</small></div>
                <div class="step-col"><small>Step 3</small></div>
            </div>
            <header>Registration</header>
            <form id="myForm" method="POST" enctype="multipart/form-data">
                <div class="forms" id="form1">
                    <span class="title">Personal Information</span>
                    
                        
                    <div class="fields">
                    <div class="input-fields">
                            <label for="">Position</label>
                            <select class="fill" name="position" required><br>
                                <option class="fill" value="student">Student</option>
                                <option class="fill" value="faculty">Faculty & Staff</option>
                            </select>
                        </div>
                        <div class="input-fields">

                            <label for="">ID Number</label>
                            <input  type="text" name="id_no" placeholder="Enter your ID Number" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Last Name</label>
                            <input  type="text" name="lname" placeholder="Enter your Last Name" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Given Name</label>
                            <input  type="text" name="fname" placeholder="Enter your Given Name" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Middle Name</label>
                            <input  type="text" name="mname" placeholder="Enter your Middle Name" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Date of Birth</label>
                            <input  type="date" name="bday" placeholder="Enter your Date of Birth" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Gender</label>
                            <select id="viewgender" class="fill" name="gender" required><br>
                                <option class="fill" value="Male">Male</option>
                                <option class="fill" value="Female">Female</option>
                            </select>
                        </div>

                        <div class="input-fields">
                            <label for="">Religion</label>
                            <input  type="text" name="religion" placeholder="Enter your Religion" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Course</label>
                            <input  type="text" name="course" placeholder="Enter your Course" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Year Level</label>
                            <select class="fill" name="year" required>
                                <option class="fill" value="1st Year">1st Year</option>
                                <option class="fill" value="2nd Year">2nd Year</option>
                                <option class="fill" value="3rd Year">3rd Year</option>
                                <option class="fill" value="4th Year">4th Year</option>
                                <option class="fill" value="5th Year">5th Year</option>
                                <option class="fill" value="Not Applicable">Not Applicable</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Section</label>
                            <input  type="text" name="section" placeholder="Enter your Section" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Contact Number</label>
                            <input  type="text" name="contact_no" placeholder="Enter your Contact Number" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Address</label>
                            <input  type="text" name="address" placeholder="Enter your Address" required>
                        </div>  

                        <div class="input-fields">
                            <label for="">Guardian</label>
                            <input  type="text" name="guardian" placeholder="Enter your Guardian's Name" required>
                        </div> 

                        <div class="input-fields">
                            <label for="">Emergency Number</label>
                            <input  type="text" name="emergency" placeholder="Guardian's Contact Number" required>
                        </div> 
                    </div>

                <div class="btn-box">
                    <button type="button" id="next">Next</button>
                </div>
                </div>
                <div class="forms" id="form2">
                    <span class="title">Health Information</span>
                    <div class="fields">
                        <div class="input-fields">
                            <label for="">Allergies</label>
                            <input type="text" name="allergy" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Asthma</label>
                            <input type="text" name="asthma" placeholder="Do you have asthma?" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Diabetes</label>
                            <input type="text" name="diabetes" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Heart Disease</label>
                            <input type="text" name="heartdisease" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Seizure</label>
                            <input type="text" name="seizure" placeholder="Do you have seizure?" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Other Health Condition</label>
                            <input type="text" name="others" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Medication</label>
                            <input type="text" name="medication" placeholder="Do you have Medication?" required>
                        </div>

                        <div class="input-fields">
                            <label for="">COVID Vaccine</label>
                            <input type="text" name="covidvax" placeholder="Enter your COVID vaccine" required>
                            
                        </div>
                        
                        <div class="input-fields">
                            <label for="">Vaccine Status</label>
                            <select class="fill" name="vacStat" required><br>
                                <option class="fill" value="Not Vaccinated">Not Vaccinated</option>
                                <option class="fill" value="Partially Vaccinated">Partially Vaccinated</option>
                                <option class="fill" value="Fully vaccinated but not boosted">Fully vaccinated but not boosted</option>
                                <option class="fill" value="Fully vaccinated and partially boosted">Fully vaccinated and partially boosted</option>
                                <option class="fill" value="Fully vaccinated and boosted twice">Fully vaccinated and boosted twice</option>
                            </select>
                        </div>
                    </div>
                
                    <div class="btn-box">
                    <button type="button" class="b1" id="back1">Back</button>
                    <button type="button" class="n1" id="next1">Next</button>
                </div>
                </div>
                <div  class="forms" id="form3">
                    <span class="title">Physical Examination</span>

                    <div class="fields">
                        <div class="input-fields">
                            <label for="">Date of Examination</label>
                            <input type="date" name="dateOfexamination" placeholder="Enter Date of Examination" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Height</label>
                            <input type="number" name="height" id="height" placeholder="Enter your Height (cm)" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Weight</label>
                            <input type="number" name="weight" id="weight" placeholder="Enter your Weight (kg)" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Body Mass Index</label>
                            <input type="text" name="bmi" id="bmi" placeholder="BMI Category" readonly>
                        </div>

                        <div class="input-fields">
                            <label for="">Blood Pressure</label>
                            <input type="text" name="bp" placeholder="Enter your Blood Pressure" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Blood Type</label>
                            <select class="fill" name="bt" required><br>
                                <option class="fill" value="O+">O+</option>
                                <option class="fill" value="O-">O-</option>
                                <option class="fill" value="O">O</option>
                                <option class="fill" value="A+">A+</option>
                                <option class="fill" value="A-">A-</option>
                                <option class="fill" value="A">A</option>
                                <option class="fill" value="B+">B+</option>
                                <option class="fill" value="B-">B-</option>
                                <option class="fill" value="B">B</option>
                                <option class="fill" value="AB+">AB+</option>
                                <option class="fill" value="AB-">AB-</option>
                                <option class="fill" value="AB-">AB</option>
                                <option class="fill" value="Unknown">Unknown</option>
                            </select>
                        </div>
                    </div>
                    <span class="title">Account Information</span>
                    <div class="fields">
                        <div class="input-fields">
                        <label for="">Smoking</label>
                            <select class="fill" name="smoking" placeholder="Do you smoke?" required><br>
                                <option class="fill" value="Never">Never</option>
                                <option class="fill" value="Rarely">Rarely</option>
                                <option class="fill" value="Sometimes">Sometimes</option>
                                <option class="fill" value="Often">Often</option>
                                <option class="fill" value="Usually">Usually</option>
                                <option class="fill" value="Always">Always</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Liquor Drinking</label>
                            <select class="fill" name="liquordrinking" placeholder="How often do you drink?" required><br>
                                <option class="fill" value="Never">Never</option>
                                <option class="fill" value="Rarely">Rarely</option>
                                <option class="fill" value="Sometimes">Sometimes</option>
                                <option class="fill" value="Often">Often</option>
                                <option class="fill" value="Usually">Usually</option>
                                <option class="fill" value="Always">Always</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Picture</label>
                            <input class="custom-file-input" type="file" name="picture" accept=".jpg, .png" required>
                        </div>
                    </div>
                    <div class="btn-box">
                        <button type="button" class="b2" id="back2">Back</button>
                        <button type="submit" class="submit" name="insertdata">Submit</button>
                    </div>
                </div>
                    
            </form>
            
        </div>
    </div>
</div>
<!-- Update Modal -->
<div class="modal" id="updateModal">
    <div class="modal-body">

        <div class="formcontainer">
        <div class="step-row">
                <div id="uprogress"></div>
                <div class="step-col"><small>Step 1</small></div>
                <div class="step-col"><small>Step 2</small></div>
                <div class="step-col"><small>Step 3</small></div>
            </div>
        <form id="updateForm" method="POST" enctype="multipart/form-data">
                <div class="forms" id="uform1">
                    <span class="title">Personal Information</span>
                    
                    <div class="fields">
                        <div class="input-fields">

                            <label for="">Student Number</label>
                            <input id="viewId"  type="text" name="id_no" placeholder="Enter your Student Number" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Last Name</label>
                            <input id="viewlname" type="text" name="lname" placeholder="Enter your Last Name" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Given Name</label>
                            <input id="viewfname" type="text" name="fname" placeholder="Enter your Given Name" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Middle Name</label>
                            <input id="viewmname" type="text" name="mname" placeholder="Enter your Middle Name" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Date of Birth</label>
                            <input id="viewbday" type="date" name="bday" placeholder="Enter your Date of Birth" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Gender</label>
                            <select id="viewgender" class="fill" name="gender" required><br>
                                <option class="fill" value="Male">Male</option>
                                <option class="fill" value="Female">Female</option>
                            </select>
                        </div>

                        <div class="input-fields">
                            <label for="">Religion</label>
                            <input id="viewreligion" type="text" name="religion" placeholder="Enter your Religion"  required>
                        </div>

                        <div class="input-fields">
                            <label for="">Course</label>
                            <input id="viewcourse" type="text" name="course" placeholder="Enter your Course" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Year Level</label>
                            <select class="fill" id="viewyear" name="year" required>
                                <option class="fill" value="1st Year">1st Year</option>
                                <option class="fill" value="2nd Year">2nd Year</option>
                                <option class="fill" value="3rd Year">3rd Year</option>
                                <option class="fill" value="4th Year">4th Year</option>
                                <option class="fill" value="5th Year">5th Year</option>
                                <option class="fill" value="Not Applicable">Not Applicable</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Section</label>
                            <input  type="text" id="viewsection" name="section" placeholder="Enter your Section" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Contact Number</label>
                            <input id="viewcontact_no" type="number" name="contact_no" placeholder="Enter your Contact Number" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Address</label>
                            <input id="viewaddress" type="text" name="address" placeholder="Enter your Address" required>
                        </div>  

                        <div class="input-fields">
                            <label for="">Guardian</label>
                            <input id="viewguardian" type="text" name="guardian" placeholder="Enter your Guardian's Name" required>
                        </div> 

                        <div class="input-fields">
                            <label for="">Emergency Number</label>
                            <input id="viewemergency"  type="number" name="emergency" placeholder="Guardian's Contact Number" required>
                        </div> 
                    </div>

                <div class="btn-box">
                    <button type="button" id="unext">Next</button>
                </div>
                </div>
                <div class="forms" id="uform2">
                    <span class="title">Health Information</span>
                    <div class="fields">
                        <div class="input-fields">
                            <label for="">Allergies</label>
                            <input id="viewallergy" type="text" name="allergy" placeholder="Please specify"  required>
                        </div>

                        <div class="input-fields">
                            <label for="">Asthma</label>
                            <input id="viewasthma" type="text" name="asthma" placeholder="Do you have asthma?" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Diabetes</label>
                            <input id="viewdiabetes" type="text" name="diabetes" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Heart Disease</label>
                            <input id="viewheartdisease" type="text" name="heartdisease" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Seizure</label>
                            <input id="viewseizure" type="text" name="seizure" placeholder="Do you have seizure?" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Other Health Condition</label>
                            <input id="viewothers" type="text" name="others" placeholder="Please specify" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Medication</label>
                            <input id="viewmedication" type="text" name="medication" placeholder="Do you have Medication?" required>
                        </div>

                        <div class="input-fields">
                            <label for="">COVID Vaccine</label>
                            <input id="viewcovidvax" type="text" name="covidvax" placeholder="Enter your COVID vaccine" required>
                            
                        </div>
                        
                        <div class="input-fields">
                            <label for="">Vaccine Status</label>
                            <select id="viewVacStat" class="fill" name="vacStat" required><br>
                                <option class="fill" value="Not Vaccinated">Not Vaccinated</option>
                                <option class="fill" value="Partially Vaccinated">Partially Vaccinated</option>
                                <option class="fill" value="Fully vaccinated but not boosted">Fully vaccinated but not boosted</option>
                                <option class="fill" value="Fully vaccinated and partially boosted">Fully vaccinated and partially boosted</option>
                                <option class="fill" value="Fully vaccinated and boosted twice">Fully vaccinated and boosted twice</option>
                            </select>
                        </div>
                    </div>
                
                    <div class="btn-box">
                    <button type="button" class="b1" id="uback1">Back</button>
                    <button type="button" class="n1" id="unext1">Next</button>
                </div>
                </div>
                <div  class="forms" id="uform3">
                    <span class="title">Physical Examination</span>

                    <div class="fields">
                        <div class="input-fields">
                            <label for="">Date of Examination</label>
                            <input id="viewdateOfexamination" type="date" name="dateOfexamination" placeholder="Enter Date of Examination" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Height</label>
                            <input id="viewheight" type="number" name="height" placeholder="Enter your Height (cm)" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Weight</label>
                            <input id="viewweight" type="number" name="weight" placeholder="Enter your Weight (kg)" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Body Mass Index</label>
                            <input id="viewbmi" type="text" name="bmi" placeholder="BMI Category" readonly>
                        </div>

                        <div class="input-fields">
                            <label for="">Blood Pressure</label>
                            <input id="viewbp" type="text" name="bp" placeholder="Enter your Blood Pressure" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Blood Type</label>
                            <select id="viewbt" class="fill" name="bt" required><br>
                                <option class="fill" value="O+">O+</option>
                                <option class="fill" value="O-">O-</option>
                                <option class="fill" value="O">O</option>
                                <option class="fill" value="A+">A+</option>
                                <option class="fill" value="A-">A-</option>
                                <option class="fill" value="A">A</option>
                                <option class="fill" value="B+">B+</option>
                                <option class="fill" value="B-">B-</option>
                                <option class="fill" value="B">B</option>
                                <option class="fill" value="AB+">AB+</option>
                                <option class="fill" value="AB-">AB-</option>
                                <option class="fill" value="AB-">AB</option>
                                <option class="fill" value="Unknown">Unknown</option>
                            </select>
                        </div>
                    </div>
                    <span class="title">Lifestyle Examination</span>
                    <div class="fields">
                    <div class="input-fields">
                        <label for="">Smoking</label>
                            <select id="viewsmoking" class="fill" name="smoking" placeholder="Do you smoke?" required><br>
                                <option class="fill" value="Never">Never</option>
                                <option class="fill" value="Rarely">Rarely</option>
                                <option class="fill" value="Sometimes">Sometimes</option>
                                <option class="fill" value="Often">Often</option>
                                <option class="fill" value="Usually">Usually</option>
                                <option class="fill" value="Always">Always</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Liquor Drinking</label>
                            <select id="viewliquordrinking" class="fill" name="liquordrinking" placeholder="How often do you drink?" required><br>
                                <option class="fill" value="Never">Never</option>
                                <option class="fill" value="Rarely">Rarely</option>
                                <option class="fill" value="Sometimes">Sometimes</option>
                                <option class="fill" value="Often">Often</option>
                                <option class="fill" value="Usually">Usually</option>
                                <option class="fill" value="Always">Always</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Picture</label>
                            <img id = "viewpicture" alt="" width="100" height="100" style="object-fit: cover;">                        </div>
                    </div>
                    <input type="hidden" id="viewunique_id" name="unique_id">
                    <input type="hidden" id="viewmedical_id" name="medical_id">

                    <div class="btn-box">
                        <button type="button" class="b2" id="uback2">Back</button>
                        <button type="submit" class="submit" name="updatedata">Update</button>
                    </div>
                </div> 
                    
            </form>
        </div>
        
    </div>
</div>
<!-- View Modal -->
<div class="modal" id="viewrecord">
  <div class="modal-body" >
    <div class="viewcontainer"> <!-- Add the ID 'invoice-content' here -->
      <div class="sticky-btn-container">
      <?php
      echo "<form id='viewform' method='post'>
      <input type='hidden' id='vunique_id' name='unique_id' onchange='submitForm(this)'>
      </form>";
      ?>
      <button type='button' class='pdf-btn' onclick='downloadPDF()'>Generate PDF</button> <!-- Change to button instead of submit -->
      <button class='printpdf-btn' onclick='printInvoice()'>Print </button>
        <!-- <button type='submit' class='pdf-btn' name='print'>Download PDF</button> -->
    </div>
    <div class="viewModal2"  style="width: 910px; position: relative; left: -50px;">
<div id="modal-content">
    <table class="header2">
        <tr>
            <td class="image-cell" >
                <img class= "item-align" src="images/clinicLogo.png" width="100px" alt="Left Image">
            </td>
            <td style="text-align: center;">
                <h5>Republic of the Philippines</h5>
                <h4>Nueva Ecija University of Science and Technology</h4>
                <h4>Medical Record</h4>
            </td>
            <td class="image-cell" >
            <div >
          <img id="vpicture" width="90px" alt="right Image">
          <span class="nav-header"></span>
            </div>            
            </td>
        </tr>
    </table>
<div class="info">
                    <table >
                        <tbody>
                            <tr>
                                <td><span class="label" for="course">Course: </span>
                                <b><span class="label" id="vcourse"></span></b>
                            </td>
                                <td>
                                <span class="label" for="course"> Year Level: </span>
                                <b><span class="label" id="vyear"></span><br></b>
                                </td>
                                <td>
                                <span class="label" for="course"> Section: </span>
                                <b><span class="label" id="vsection"></span><br></b>
                                </td>
                                <td>
                                <span class="label" for="course">SY: <b>2024-2025</b></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
        
            <!-- Personal Information Table with added margin -->
            <table class="personal-info-table">
                <tr>
                    <td colspan="3">
                        <div class="section-title">Personal Information</div>
                        <table class="b">
                                <tbody>
                                    <tr>
                                        <td><span class="label" for="name">Name:</span></td>
                                        <td>
                                            <b><span id="vlname"></span></b>
                                            <b><span>,</span></b></b>
                                            <b><span id="vfname"></span></b>
                                            <b><span> </span></b>
                                            <b><span id="vmname"></span></b>
                                            <b><span>.</span></b>
                                        </td>
                                        <td><span class="label" for="gender">Gender:</span></td>
                                        <td><b><span id="vgender"></span></td></b>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="religion">Religion:</span></td>
                                        <td><b><span id="vreligion"><</span></b></td>
                                        <td><span class="label" for="address">Home Address:</span></td>
                                        <td><b><span id="vaddress"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="DateOfBirth">Date of Birth:</span></td>
                                        <td><b><span id="vbday"></span></b></td>
                                        <td><span class="label" for="parent">Parent / Guardian:</span></td>
                                        <td><b><span id="vguardian"></b></span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="age">Age:</span></td>
                                        <td><b><span id="vage"></span></b></td>
                                        <td><span class="label" for="contact">Emergency Contact:</span></td>
                                        <td><b><span id="vemergency"></span></b></td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                    </td>
                </tr>
            </table>
            <!-- Second row for Health Info, Physical Examination, and Lifestyle Info -->
            <table >
                <tr>
                    <!-- Health Information -->
                    <td>
                        <div class="section-title">Health Information</div>
                        <table class="b">
                                <tbody>
                                    <tr>
                                        <td><span class="label" for="allergies">Allergies:</span></td>
                                        <td><b><span id="vallergy"></span></b></td>
                                        <td><span class="label" for="other">Other Health Condition:</span></td>
                                        <td><b><span id="vothers"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="asthma">Asthma:</span></td>
                                        <td><b><span id="vasthma"></span></b></td>
                                        <td><span class="label" for="medication">Medication:</span></td>
                                        <td><b><span id="vmedication"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="diabetes">Diabetes:</span></td>
                                        <td><b><span id="vdiabetes"></span></b></td>
                                        <td><span class="label" for="heartDisease">Heart Disease:</span></td>
                                        <td><b><span id="vheartdisease"></span><br></b></td>
                                        
                                    </tr>
                                    <tr>
                                        
                                        <td><span class="label" for="vaccine">COVID Vaccine:</span></td>
                                        <td><b><span id="vcovidvax"></span></b></td>
                                        <td><span class="label" for="SeizureD">Seizure Disorder:</span></td>
                                        <td><b><span id="vseizure"></span></b></td>
                                        
                                    </tr>
                                    <tr>
                                    <td><span class="label" for="vaccineStat">Vaccine Status:</span></td>
                                    <td><b><span id="vcvaxstatus"></span></b></td>
                                    </tr>    
                                </tbody>
                            </table>
                    </td>
        
                    <!-- Divider between Health Info and Physical Examination -->
                    <td class="divider"></td>
        
                    <!-- Physical Examination -->
                    <td class="physicalyexamlayout" style="position: relative; top: -35px;">

                        <div class="section-title">Physical Examination</div>
                        <table class="b">
                            <tbody>
                                <tr>
                                    <td><span class="label" for="exam_date">Date of Examination:</span></td>
                                    <td><b><span id="vdateOfexamination"></span></b></td>
                                </tr>
                                <tr>
                                    <td><span class="label" for="height">Height:</span></td>
                                    <td><b><span id="vheight"></span></b></td>
                                </tr>
                                <tr>
                                    <td><span class="label" for="weight">Weight:</span></td>
                                    <td><b><span id="vweight"></span></b></td>
                                </tr>
                                <tr>
                                    <td><span class="label" for="bp">Blood Pressure:</span></td>
                                    <td><b><span id="vbp"></span></b></td>
                                </tr>
                                <tr>
                                    <td><span class="label" for="blood_type">Blood Type:</span></td>
                                    <td><b><span id="vbt"></span></b></td>
                                </tr>            
                            </tbody>
                            
                        </table>
                    </td>
                </tr>
                <tr style="position: relative; top: -70px;">
                    <!-- Lifestyle Information -->
                    <td colspan="2"></td> <!-- Empty cell for spacing -->
                    <td>
                        <div class="section-title">Lifestyle Information</div>
                        <table class="b">
                            <tbody>
                                <tr>
                                    <td><span class="label">Smoking:</span></td>
                                    <td><b><span id="vsmoking"></span></b></td>
                                    <td><span class="label">Liquor Drinking:</span></td>
                                    <td><b><span id="vliquordrinking"></span></b></td>
                                </tr>     
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            <div class="section-title" style="position: relative; top: -90px;">Medicine Prescription</div>

            <table id="medicalhistory" class="personal-info-table" style="position: relative; top: -90px; border: 1px solid #ccc;" >

                <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Dosage</th>
                    <th>Quantity</th>
                    <th>Case</th>
                    <th>Date of Prescription</th>
                </tr>
                </thead>  
                <tbody style="text-align:center;">
                
                  </tbody>
            </table>
            <!-- Certified By -->
            <div class="bottom-info">
            <span class="certifiedBy">
<p>Certified by: <b>
    <?php 
$user_id = $_SESSION['user_id']; // or from $_GET, $_POST, etc.

$sql = "SELECT * FROM user_tbl WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // Assuming user_id is an integer (change "i" if it's another type)
$stmt->execute();

// Get the result set from the executed query
$result = $stmt->get_result();

// Fetch the single row where user_id matches
$row = $result->fetch_assoc(); // Fetch as an associative array

// Check if any result is returned
if ($row) {
    // Access the row data here, e.g.:
    echo $row['lname'] . ", " . $row['fname'];
    echo "<br><span id='vbt'>" . $row['position'] . "</span><br>";
    // Add other fields here...
} else {
    echo "No record found for the specified user_id.";
}

// Close the statement
$stmt->close();?></b></p>
<br>
<br>
<br>
</span>
            </div>

</div>
</div>
</div>
</div>
</div>
        <!-- Print Form -->
        <div class="pmodal" id="printForm" >
        <div class="modal-body" >
        <div class="presformcontainer">
            <header>Print Medical Record</header>
            <form id="printForm" action="Print" method="POST" target="printWindow">
                <div class="forms">
                    <div class="fieldsMarginTop">
                    <div class="input-fields">
                            <label for="">Gender</label>
                            <select id="viewgender" class="fill" name="gender" required><br>
                                <option value="All">All</option>
                                <option class="fill" value="Male">Male</option>
                                <option class="fill" value="Female">Female</option>
                            </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Age</label>
                            <select class="fill" id="blood" onchange="filterTable()" name="age"><br>
                            <option value="All">All</option>
                          <?php
                             // Query to fetch distinct blood types from the database
                                    $query = "SELECT DISTINCT age FROM personalinfo_tbl ORDER BY age ASC";
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        // Loop through the result set and output each distinct blood type in an option element
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option>" . htmlspecialchars($row["age"]) . "</option>";
                                        }
                                    } else {
                                        echo "<option>No age available</option>";
                                    }
                            ?>
                        </select>
                        </div>
                    <div class="input-fields">
                            <label for="">Course</label>
                            <select class="fill" id="blood" onchange="filterTable()" name="course"><br>
                            <option value="All">All</option>
                          <?php
                             // Query to fetch distinct blood types from the database
                                    $query = "SELECT DISTINCT course FROM personalinfo_tbl ORDER BY course ASC";
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        // Loop through the result set and output each distinct blood type in an option element
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option>" . htmlspecialchars($row["course"]) . "</option>";
                                        }
                                    } else {
                                        echo "<option>No course available</option>";
                                    }
                            ?>
                        </select>
                        </div>

                        <div class="input-fields">
                            <label for="">Year Level</label>
                            <select class="fill" name="year" required><br>
                                <option value="All">All</option>
                                <option class="fill" value="1st Year">1st Year</option>
                                <option class="fill" value="2nd Year">2nd Year</option>
                                <option class="fill" value="3rd Year">3rd Year</option>
                                <option class="fill" value="4th Year">4th Year</option>
                                <option class="fill" value="5th Year">5th Year</option>
                                <option class="fill" value="Not Applicable">Not Applicable</option>
                            </select>
                        </div>

                        <div class="input-fields">
                            <label for="">Section</label>
                            <select class="fill" id="blood" onchange="filterTable()" name="section"><br>
                            <option value="All">All</option>
                          <?php
                             // Query to fetch distinct blood types from the database
                                    $query = "SELECT DISTINCT section FROM personalinfo_tbl ORDER BY section ASC";
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        // Loop through the result set and output each distinct blood type in an option element
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option>" . htmlspecialchars($row["section"]) . "</option>";
                                        }
                                    } else {
                                        echo "<option>No section available</option>";
                                    }
                            ?>
                        </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Blood Type</label>
                            <select class="fill" id="blood" onchange="filterTable()" name="bloodtype" required><br>
                            <option value="All">All</option>
                          <?php
                             // Query to fetch distinct blood types from the database
                                    $query = "SELECT DISTINCT bloodtype FROM medicalinfo_tbl ORDER BY bloodtype ASC";
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        // Loop through the result set and output each distinct blood type in an option element
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option>" . htmlspecialchars($row["bloodtype"]) . "</option>";
                                        }
                                    } else {
                                        echo "<option>No section available</option>";
                                    }
                            ?>
                            </select>
                        </div>

                    </div>
                
                    <div class="btn-print">
                        <button type="submit" class="submit" name="print_filter" onclick="openPrintWindow()">Print</button>
                    </div>
                </div>
                    
            </form>
            
        </div>
        </div>
        </div>
  <div id="overlay"></div>
    <div id="utoastBox" aria-live="polite"></div>
    <div id="toastBox" aria-live="polite"></div>
    
<script>
            const form = document.getElementById("myForm");
            

            var form1 = document.getElementById("form1");
            var form2 = document.getElementById("form2");
            var form3 = document.getElementById("form3");

            var next = document.getElementById("next");
            var back1 = document.getElementById("back1");
            var next1 = document.getElementById("next1");
            var back2 = document.getElementById("back2");

            var progress = document.getElementById("progress");
            

            next.addEventListener('click', (e) => {
              const inputs =  form1.getElementsByTagName('input');
              let errorMessage = '';
              for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].value.trim() === '') {
                  errorMessage = 'Please fill out all fields.';
                  break;
                }
              }
              if (errorMessage) {
                showToast(invalid);
                return false; // Prevent form submission
              } else {
                nextForm();
                return true; // Allow form submission
              }
                
                function nextForm(){
                    form1.style.left = "-2600px";
                    form2.style.left = "40px";
                    progress.style.width = "600px";
                    showToast(success);
                }
            })
            next1.addEventListener('click', (e) => {
              const inputs =  form2.getElementsByTagName('input');
              let errorMessage = '';
              for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].value.trim() === '') {
                  errorMessage = 'Please fill out all fields.';
                break;
                }
              }
              if (errorMessage) {
                showToast(invalid);
                return false; // Prevent form submission
              } else {
                nextForm2();
                return true; // Allow form submission
              }
              
              function nextForm2(){
                    form2.style.left = "-2600px";
                    form3.style.left = "40px";
                    progress.style.width = "920px";
                    showToast(success);
              }
              
          })
          let toastBox = document.getElementById('toastBox');
          let success = '<span>&#10004;</span> Successfully Submitted';
          let error = '<span>&#10008;</span> Please fix the error';
          let invalid = "<span>&#33;</span> Invalid input, Please complete the form";

          function showToast(msg) {
            let toast = document.createElement('div');
            toast.classList.add('toast');
            toast.innerHTML = msg;
            toastBox.appendChild(toast);

            if (msg.includes('Successfully')) {
              toast.classList.add('success');
            } else if (msg.includes('Please fix the error')) {
              toast.classList.add('error');
            } else if (msg.includes('Invalid input')) {
              toast.classList.add('invalid');
            }

            setTimeout(() => {
              toast.remove();
            }, 2000);

            }
            back1.onclick = function(){
                form1.style.left = "40px";
                form2.style.left = "2600px";
                progress.style.width = "300px";
            }
            back2.onclick = function(){
                form2.style.left = "40px";
                form3.style.left = "2600px";
                progress.style.width = "600px";
            }

</script>
<script>
            const uform = document.getElementById("updateForm");
            const uinputs = uform.getElementsByTagName('input');

            var uform1 = document.getElementById("uform1");
            var uform2 = document.getElementById("uform2");
            var uform3 = document.getElementById("uform3");

            var unext = document.getElementById("unext");
            var uback1 = document.getElementById("uback1");
            var unext1 = document.getElementById("unext1");
            var uback2 = document.getElementById("uback2");

            var uprogress = document.getElementById("uprogress");
            

            unext.addEventListener('click', (e) => {
              
                let uerrorMessage = '';
                for (let i = 0; i < 12; i++) {
                    if (uinputs[i].value.trim() === '') {
                        uerrorMessage = 'Please fill out all fields.';
                        break;
                    }
                }
                if (uerrorMessage) {
                    ushowToast(uinvalid);
                    return false; // Prevent form submission
                } else {
                    unextForm();
                    return true; // Allow form submission
                }
                
                function unextForm(){
                    uform1.style.left = "-2600px";
                    uform2.style.left = "40px";
                    uprogress.style.width = "600px";
                    ushowToast(usuccess);
                }
            })
            unext1.addEventListener('click', (e) => {
              
              let uerrorMessage = '';
              for (let i = 0; i < 20; i++) {
                  if (uinputs[i].value.trim() === '') {
                      uerrorMessage = 'Please fill out all fields.';
                      break;
                  }
              }
              if (uerrorMessage) {
                  ushowToast(uinvalid);
                  return false; // Prevent form submission
              } else {
                  unextForm2();
                  return true; // Allow form submission
              }
              
              function unextForm2(){
                    uform2.style.left = "-2600px";
                    uform3.style.left = "40px";
                    uprogress.style.width = "920px";
                    ushowToast(usuccess);
              }
              
          })

            let utoastBox = document.getElementById('utoastBox');
                    let usuccess = '<span>&#10004;</span> Sucessfully Submitted';
                    let uerror = '<span>&#10008;</span> PLease fix the error';
                    let uinvalid = "<span>&#33;</span> Invalid input, Please complete the form";
            function ushowToast(msg){
                let utoast = document.createElement('div');
                utoast.classList.add('utoast');
                utoast.innerHTML = msg;
                utoastBox.appendChild(utoast);

                if(msg.includes('error')){
                    utoast.classList.add('uerror');
                }
                if(msg.includes('invalid')){
                    utoast.classList.add('uinvalid');
                }

                setTimeout(() => {
                    utoast.remove();
                }, 2000);

            }
            uback1.onclick = function(){
                uform1.style.left = "40px";
                uform2.style.left = "2600px";
                uprogress.style.width = "300px";
            }
            uback2.onclick = function(){
                uform2.style.left = "40px";
                uform3.style.left = "2600px";
                uprogress.style.width = "600px";
            }

</script>
<script>
    const heightInput = document.getElementById("height");
    const weightInput = document.getElementById("weight");
    const bmiField = document.getElementById("bmi");

    // Event listeners for input changes
    heightInput.addEventListener("input", calculateBMICategory);
    weightInput.addEventListener("input", calculateBMICategory);

    function calculateBMICategory() {
        const heightInCm = parseFloat(heightInput.value);
        const weight = parseFloat(weightInput.value);

        // Convert height from centimeters to meters
        const heightInMeters = heightInCm / 100;

        // Make sure both height and weight are valid inputs
        if (heightInMeters > 0 && weight > 0) {
            const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(2);

            // Display the appropriate BMI category
            displayBMICategory(bmi);
        } else {
            bmiField.value = ''; // Clear the field if invalid input
        }
    }

    function displayBMICategory(bmi) {
        if (bmi < 18.5) {
            bmiField.value = "Underweight";
        } else if (bmi >= 18.5 && bmi < 24.9) {
            bmiField.value = "Normal weight";
        } else if (bmi >= 25 && bmi < 29.9) {
            bmiField.value = "Overweight";
        } else {
            bmiField.value = "Obese";
        }
    }
</script>
<script>
    const vheightInput = document.getElementById("viewheight");
    const vweightInput = document.getElementById("viewweight");
    const vbmiField = document.getElementById("viewbmi");

    // Event listeners for input changes
    vheightInput.addEventListener("input", vcalculateBMICategory);
    vweightInput.addEventListener("input", vcalculateBMICategory);

    function vcalculateBMICategory() {
        const vheightInCm = parseFloat(vheightInput.value);
        const vweight = parseFloat(vweightInput.value);

        // Convert height from centimeters to meters
        const vheightInMeters = vheightInCm / 100;

        // Make sure both height and weight are valid inputs
        if (vheightInMeters > 0 && vweight > 0) {
            const vbmi = (vweight / (vheightInMeters * vheightInMeters)).toFixed(2);

            // Display the appropriate BMI category
            vdisplayBMICategory(vbmi);
        } else {
            vbmiField.value = ''; // Clear the field if invalid input
        }
    }

    function vdisplayBMICategory(vbmi) {
        if (vbmi < 18.5) {
            vbmiField.value = "Underweight";
        } else if (vbmi >= 18.5 && vbmi < 24.9) {
            vbmiField.value = "Normal weight";
        } else if (vbmi >= 25 && vbmi < 29.9) {
            vbmiField.value = "Overweight";
        } else {
            vbmiField.value = "Obese";
        }
    }
</script>
<script>
    function filterTable() {
        var dropdown = document.getElementById("blood");
        var selectedValue = dropdown.value;
        var table = document.getElementById("tbl");
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) {
            var row = rows[i];
            var medicine = row.cells[4].textContent.trim();

            if (selectedValue === "All" || medicine === selectedValue) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    }
</script>
<script>
    // Attach keypress event to the input field
    document.getElementById("searchInput").addEventListener("keypress", function(event) {
        // Check if the key pressed is "Enter"
        if (event.key === "Enter") {
            event.preventDefault(); // Prevent the default form submit action
            document.getElementById("searchForm").submit(); // Manually submit the form
        }
    });
</script>

<script>
function confirmArchive(userId) {
    // Show confirmation alert
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to archive this record?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!',
        cancelButtonText: 'No, cancel!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Find the form associated with the user ID and submit it
            var form = document.getElementById('archiveForm_' + userId);
            form.submit();
        }
    });
}
function submitForm(input) {
    if (input.value.trim() !== '') { // Check if the input is not empty
        document.getElementById('viewform').submit(); // Submit the form
    }
}
</script>

<script>
        document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".viewbtn").forEach(button => {
        button.addEventListener("click", function() {
            const historyId = this.getAttribute("data-history-id"); // Get historyId from button's data attribute
            console.log("Button clicked, historyId:", historyId); // Debugging line
            
            fetch("<?php echo $_SERVER['PHP_SELF']; ?>?action=viewHistory&id=" + historyId)
                .then(response => response.json())
                .then(data => populateTable(data))
                .catch(error => console.error("Error:", error));
        });
    });
});

        function populateTable(data) {
            const tableBody = document.getElementById("medicalhistory").getElementsByTagName("tbody")[0];
            tableBody.innerHTML = ""; // Clear existing rows

            data.forEach(row => {
                const newRow = tableBody.insertRow();

                // Insert each cell
                const medCell = newRow.insertCell();
                medCell.textContent = row.med_name;

                const dosageCell = newRow.insertCell();
                dosageCell.textContent = row.dosage;

                const quantityCell = newRow.insertCell();
                quantityCell.textContent = row.quantity;

                const caseCell = newRow.insertCell();
                caseCell.textContent = row.complaint;

                const dateCell = newRow.insertCell();
                dateCell.textContent = row.dOprescription;
            });
        }
    </script>
</body>

<?php
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertdata']))
{
    $position = htmlspecialchars($_POST["position"]);
    $id_no = htmlspecialchars($_POST["id_no"]);
    $lname = htmlspecialchars($_POST["lname"]);
    $fname = htmlspecialchars($_POST["fname"]);
    $mname = htmlspecialchars($_POST["mname"]);
    $bday = htmlspecialchars($_POST["bday"]);
    $gender = htmlspecialchars($_POST["gender"]);
    $religion = htmlspecialchars($_POST["religion"]);
    
    // Convert the birth date string into a DateTime object
    $birthdate_obj = new DateTime($bday);
    
    // Get the current date
    $current_date = new DateTime();
    
    // Calculate the difference between the current date and the birth date
    $age_interval = $current_date->diff($birthdate_obj);
    $age = $age_interval->y;
    
    $course = htmlspecialchars($_POST["course"]);
    $year = htmlspecialchars($_POST["year"]);
    $section = htmlspecialchars($_POST["section"]);
    $contact_no = htmlspecialchars($_POST["contact_no"]);
    $address = htmlspecialchars($_POST["address"]);
    $guardian = htmlspecialchars($_POST["guardian"]);
    $emergency = htmlspecialchars($_POST["emergency"]);
    $allergy = htmlspecialchars($_POST["allergy"]);
    $asthma = htmlspecialchars($_POST["asthma"]);
    $diabetes = htmlspecialchars($_POST["diabetes"]);
    $heartdisease = htmlspecialchars($_POST["heartdisease"]);
    $seizure = htmlspecialchars($_POST["seizure"]);
    $others = htmlspecialchars($_POST["others"]);
    $medication = htmlspecialchars($_POST["medication"]);
    $covidvax = htmlspecialchars($_POST["covidvax"]);
    
    // Check which checkboxes were checked
    $vaccineStatus = $_POST["vacStat"]; // Assuming this is an array, no need for htmlspecialchars here if you sanitize it later
    
    $dateOfexamination = htmlspecialchars($_POST["dateOfexamination"]);
    $height = htmlspecialchars($_POST["height"]);
    $weight = htmlspecialchars($_POST["weight"]);
    $bp = htmlspecialchars($_POST["bp"]);
    $bt = htmlspecialchars($_POST["bt"]);
    $bmi_category = htmlspecialchars($_POST["bmi"]);
    $smoking = htmlspecialchars($_POST["smoking"]);
    $liquordrinking = htmlspecialchars($_POST["liquordrinking"]);
         // File details
         // File upload handling
         $target_dir = "uploads/";
         $original_file_name = basename($_FILES["picture"]["name"]);
         $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
         $filename = $id_no . " " . $lname . " " . $fname;
         // Sanitize the first name to prevent any potential file system issues
         $sanitized_firstname = preg_replace("/[^a-zA-Z0-9]/", "", $filename);
         
         // Generate the new file name using the first name
         $new_file_name = $sanitized_firstname . '.' . $file_extension;
         
         // Set the target file path with the new file name
         $target_file = $target_dir . $new_file_name;
// Check if ID Number already exists
$stmt = $conn->prepare("SELECT * FROM personalinfo_tbl WHERE id_no = ?");
$stmt->bind_param("s", $_POST["id_no"]);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "<script>Swal.fire('Error', 'ID Number already exists.', 'error');</script>";
    exit;
}
        // Move the uploaded file to the target directory
        if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            die("Sorry, there was an error uploading your file.");
        }
        $stmt = $conn->prepare("INSERT INTO personalinfo_tbl (id_no, position, fname, lname, mname, gender, religion, bday, age, contact_no, course, year_level, section, address, guardian, emergency_no, picture) 
                    VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,  ?, ?,?, ?, ?)");

        $stmt2 = $conn->prepare("INSERT INTO medicalinfo_tbl (unique_id, allergy, asthma, diabetes, heartdisease, seizure, others, medication, vaccine, vaccine_status, dOexamination, height, weight, bmi, bloodpressure, bloodtype, smoking, liquor) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        
        if ($stmt === false && $stmt2 === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("ssssssssissssssis",$id_no, $position, $fname, $lname, $mname, $gender, $religion, $bday, $age, $contact_no, $course, $year, $section, $address, $guardian, $emergency, $target_file);
        $stmt->execute();
        $last_user_id = $conn->insert_id;
        $stmt2->bind_param("issssssssssiisssss",$last_user_id, $allergy, $asthma, $diabetes, $heartdisease, $seizure, $others, $medication, $covidvax, $vaccineStatus, $dateOfexamination, $height, $weight, $bmi_category, $bp, $bt, $smoking, $liquordrinking);
        $stmt2->execute();
        if ($stmt->affected_rows > 0 && $stmt2->affected_rows > 0) {
            echo "<script>
            Swal.fire('Success', 'We have successfully registered the user!', 'success').then(() => {
                                window.location.replace('Medical Record');
                                });
            
                  </script>";
      } else {
        echo "<script>Swal.fire('Error', 'Failed to register the user. Please try again.', 'error');</script>";
    
      }
          } 
          if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatedata']))
          {
            $unique_id = htmlspecialchars($_POST["unique_id"]);
            $medical_id = htmlspecialchars($_POST["medical_id"]);
            $id_no = htmlspecialchars($_POST["id_no"]);
            $lname = htmlspecialchars($_POST["lname"]);
            $fname = htmlspecialchars($_POST["fname"]);
            $mname = htmlspecialchars($_POST["mname"]);
            $bday = htmlspecialchars($_POST["bday"]);
            $gender = htmlspecialchars($_POST["gender"]);
            $religion = htmlspecialchars($_POST["religion"]);
            
            // Convert the birth date string into a DateTime object
            $birthdate_obj = new DateTime($bday);
            
            // Get the current date
            $current_date = new DateTime();
            
            // Calculate the difference between the current date and the birth date
            $age_interval = $current_date->diff($birthdate_obj);
            $age = $age_interval->y;
            
            $course = htmlspecialchars($_POST["course"]);
            $year = htmlspecialchars($_POST["year"]);
            $section = htmlspecialchars($_POST["section"]);
            $contact_no = htmlspecialchars($_POST["contact_no"]);
            $address = htmlspecialchars($_POST["address"]);
            $guardian = htmlspecialchars($_POST["guardian"]);
            $emergency = htmlspecialchars($_POST["emergency"]);
            $allergy = htmlspecialchars($_POST["allergy"]);
            $asthma = htmlspecialchars($_POST["asthma"]);
            $diabetes = htmlspecialchars($_POST["diabetes"]);
            $heartdisease = htmlspecialchars($_POST["heartdisease"]);
            $seizure = htmlspecialchars($_POST["seizure"]);
            $others = htmlspecialchars($_POST["others"]);
            $medication = htmlspecialchars($_POST["medication"]);
            $covidvax = htmlspecialchars($_POST["covidvax"]);
            
            // Assuming this is an array, htmlspecialchars might not apply directly here, process it safely later if needed
            $vaccineStatus = htmlspecialchars($_POST["vacStat"]);
            
            $dateOfexamination = htmlspecialchars($_POST["dateOfexamination"]);
            $height = htmlspecialchars($_POST["height"]);
            $weight = htmlspecialchars($_POST["weight"]);
            $bmi_category = htmlspecialchars($_POST["bmi"]);
            $bp = htmlspecialchars($_POST["bp"]);
            $bt = htmlspecialchars($_POST["bt"]);
            $smoking = htmlspecialchars($_POST["smoking"]);
            $liquordrinking = htmlspecialchars($_POST["liquordrinking"]);
                  // Begin a transaction
          $conn->begin_transaction();
          
          try {
              // Update personalinfo_tbl
              $sql1 = "UPDATE personalinfo_tbl SET 
                      id_no = ?, 
                      lname = ?, 
                      fname = ?, 
                      mname = ?, 
                      gender = ?, 
                      religion = ? ,
                      bday = ?, 
                      age = ?, 
                      contact_no = ?, 
                      course = ?,
                      year_level = ?, 
                      section = ?,  
                      address = ? ,
                      guardian = ?, 
                      emergency_no = ?
                      WHERE unique_id = ?";
                       
              $stmt1 = $conn->prepare($sql1);
              $stmt1->bind_param("sssssssisssssssi",$id_no, $lname, $fname, $mname, $gender, $religion, $bday,
              $age, $contact_no, $course, $year, $section, $address, $guardian,$emergency,  $unique_id);
              $stmt1->execute();
          
              // Update medicalinfo_tbl
              $sql2 = "UPDATE medicalinfo_tbl SET 
                       allergy = ?, 
                       asthma = ?, 
                       diabetes = ?, 
                       heartdisease = ?, 
                       seizure = ?, 
                       others = ?,
                       medication = ?, 
                       vaccine = ?, 
                       vaccine_status = ?, 
                       dOexamination = ?, 
                       height	 = ?, 
                       weight = ?,
                       bmi = ?, 
                       bloodpressure = ?, 
                       bloodtype = ?, 
                       smoking = ?, 
                       liquor = ?
                       WHERE medical_id = ? && unique_id = ?";
                       
              $stmt2 = $conn->prepare($sql2);
              $stmt2->bind_param("ssssssssssiisssssii", $allergy, $asthma, $diabetes, $heartdisease, $seizure, $others, 
               $medication, $covidvax, $vaccineStatus, $dateOfexamination, $height, $weight, $bmi_category,$bp, $bt, $smoking, $liquordrinking, $medical_id, $unique_id);
              $stmt2->execute();
          
              // If both queries succeed, commit the transaction
              $conn->commit();
              echo "<script>
              Swal.fire('Success', 'We have successfully updated the record!', 'success').then(() => {
                                window.location.replace('Medical Record');
                                });
                    </script>";
          } catch (Exception $e) {
              // If there is an error, rollback the transaction
              $conn->rollback();
 echo "<script>Swal.fire('Error', 'Failed to update the record. Please try again.', 'error');</script>";             

          }
                    } 
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive'])) {
                        $user_id = $_POST['user_id'];
                        $sql = "UPDATE personalinfo_tbl SET archive_status = 'archived' WHERE unique_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                    
                        if ($stmt->execute()) {
                            echo "<script>
                                Swal.fire('Success', 'Archived successfully!', 'success').then(() => {
                                window.location.replace('Medical Record');
                                });
                            </script>";
                        } else {
                            echo "<script>
                                Swal.fire('Error', 'Error archiving record!', 'error');
                            </script>";
                        }
                    
                        $stmt->close();
                    }
                    function fetchUsers($conn, $search_query = null) {
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
                            $search_query = $_POST['search'];
                    
                            if ($search_query !== null && $search_query != "") {
                                // If there's a search term, use it in the WHERE clause
                                $stmt = $conn->prepare("
                                    SELECT * 
                FROM personalinfo_tbl 
                JOIN medicalinfo_tbl 
                ON personalinfo_tbl.unique_id = medicalinfo_tbl.unique_id 
                WHERE (personalinfo_tbl.fname LIKE ? 
                OR personalinfo_tbl.lname LIKE ? 
                OR CONCAT(personalinfo_tbl.fname, ' ', personalinfo_tbl.lname) LIKE ? 
                OR personalinfo_tbl.id_no LIKE ?) AND archive_status != 'archived'
                                ");
                                $search_term = $search_query . '%';
                                // Bind parameters for first name, last name, full name, and ID number
                                $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
                                $stmt->execute();
                                return $stmt->get_result(); // Fetch the results from the query
                            } else if ($search_query == "") {
                                // If no search term, show all records
                                $sql = "SELECT * 
                                FROM personalinfo_tbl 
                                JOIN medicalinfo_tbl 
                                ON personalinfo_tbl.unique_id = medicalinfo_tbl.unique_id 
                                WHERE personalinfo_tbl.archive_status != 'archived'
                                ORDER BY personalinfo_tbl.fname ASC
                                ";
                                return $conn->query($sql); // Execute the non-prepared query for all records
                            }
                        } else {
                            // If no search term, show all records
                            $sql = "SELECT * 
                            FROM personalinfo_tbl 
                            JOIN medicalinfo_tbl 
                            ON personalinfo_tbl.unique_id = medicalinfo_tbl.unique_id 
                            WHERE personalinfo_tbl.archive_status != 'archived'
                            ORDER BY personalinfo_tbl.fname ASC
                            ";
                            return $conn->query($sql); // Execute the non-prepared query for all records
                        }
                    }
                    ?>

<iframe id="printFrame" style="display:none;"></iframe>
<iframe id="printallFrame" style="display:none;"></iframe>
<script>
    function printInvoice() {
    // Get the content of the specific div
    var printContent = document.getElementById("modal-content").innerHTML;

    // Create a new window
    var printFrame = document.getElementById("printFrame").contentWindow;
    printFrame.document.open();
    // Write the div content into the new window
    printFrame.document.write(`
        <html>
        <head>
            <title>Print Invoice</title>
            <link rel="stylesheet" href="userStyle.css" />
             <style>
             @media print {
                    #modal-content {
                        display: block;
        </style>
        </head>
        <body>
            <div id="modal-content">
                ${printContent}
            </div>

        </body>
        </html>
    `);

    // Close the document to finish writing
    printFrame.document.close();
    // Give it a slight delay to ensure the document is ready
    setTimeout(function() {
        // Trigger the print dialog
        printFrame.focus();
        printFrame.print();
    }, 500);
}

function openPrintWindow() {
    // Open a new window with specified size
    const form = document.getElementById("printForm");
    var printAll = window.open("", "printWindow", "width=1000,height=600");
    form.submit();
    // Give it a slight delay to ensure the document is ready
    setTimeout(function() {
        // Trigger the print dialog
        printAll.print();

        // Close the print window after printing
        printAll.close();
    }, 5000);
}
function downloadPDF() {
    const { jsPDF } = window.jspdf;

    // Select the div to capture
    var modalContent = document.getElementById("modal-content");

    // Define margins
    const marginLeft = 10; // Left margin for PDF
    const marginTop = 10;  // Top margin for PDF

    // Use html2canvas to capture the content with styles
    html2canvas(modalContent, { scale: 2 }).then(function (canvas) {
        // Get the image data from the canvas
        var imgData = canvas.toDataURL("image/png");

        // Create a new jsPDF instance
        var pdf = new jsPDF("p", "mm", "a4");

        // Set image width and height to fit the A4 page with margin
        var imgWidth = 210 - 2 * marginLeft; // A4 width minus margins in mm
        var imgHeight = (canvas.height * imgWidth) / canvas.width;

        // Add the image to the PDF with padding/margin
        pdf.addImage(imgData, 'PNG', marginLeft, marginTop, imgWidth, imgHeight);

        // Save the generated PDF
        pdf.save("medical_record.pdf");
    });
}
    </script>

</html>
</span>