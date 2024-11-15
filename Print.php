<?php
session_start();
include ('conn.php');

?>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Medical Record</title>
  <link rel="stylesheet" href="userStyle.css" />
  <script defer src="scriptpop.js"></script>
  
<!-- jsPDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <!-- Font Awesome Cdn Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
    @media print {
    body {
        
        -webkit-print-color-adjust: exact; /* Ensures colors are printed exactly */

    }
    
    .page-break {
        page-break-before: always; /* Forces a page break before this element */
    }
    .no-print {
        display: none; /* Hides elements you don't want to print */
    }


}

  </style>
</head>
<body style="background: white;">
</body>
<?php
function getFilteredRecords($conn, $filters) {
    // Base query for fetching data
    $query = "SELECT * FROM personalinfo_tbl INNER JOIN medicalinfo_tbl ON personalinfo_tbl.unique_id = medicalinfo_tbl.unique_id WHERE 1=1";

    // Filtering based on gender
    if ($filters['gender'] !== "All") {
        $gender = mysqli_real_escape_string($conn, $filters['gender']);
        $query .= " AND personalinfo_tbl.gender = '$gender'";
    }

    // Filtering based on age
    if (isset($filters['age']) && $filters['age'] !== "All") {
        $age = mysqli_real_escape_string($conn, $filters['age']);
        $query .= " AND personalinfo_tbl.age = '$age'";
    }

    // Filtering based on course
    if ($filters['course'] !== "All") {
        $course = mysqli_real_escape_string($conn, $filters['course']);
        $query .= " AND personalinfo_tbl.course = '$course'";
    }

    // Filtering based on year
    if ($filters['year'] !== "All") {
        $year = mysqli_real_escape_string($conn, $filters['year']);
        $query .= " AND personalinfo_tbl.year_level = '$year'";
    }

    // Filtering based on section
    if ($filters['section'] !== "All") {
        $section = mysqli_real_escape_string($conn, $filters['section']);
        $query .= " AND personalinfo_tbl.section = '$section'";
    }

    // Filtering based on blood type
    if ($filters['blood'] !== "All") {
        $bloodtype = mysqli_real_escape_string($conn, $filters['blood']);
        $query .= " AND medicalinfo_tbl.bloodtype = '$bloodtype'";
    }
    // Execute the query
    return $conn->query($query);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['print_filter'])) {
    // Get filters from POST request
    $filters = [
        'gender' => $_POST['gender'],
        'age' => $_POST['age'],
        'course' => $_POST['course'],
        'year' => $_POST['year'],
        'section' => $_POST['section'],
        'blood' => $_POST['bloodtype'],
    ];

    // Fetch filtered records
    $result = getFilteredRecords($conn, $filters);
    ?>
    <div id="print-content">
        <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div id="modal-content">
                <table class="header2">
                    <tr>
                        <td style=" item-align: center">
                            <img class="item-align" src="images/clinicLogo.png" width="100px" alt="Left Image">
                        </td>
                        <td style="text-align: center;">   
                            <h5>Republic of the Philippines</h5>
                            <h4>Nueva Ecija University of Science and Technology</h4>
                            <h4>Medical Record</h4>
                        </td>
                        <td class="image-cell" >
                        <div>
                    <img src="<?php echo htmlspecialchars($row['picture']); ?>" width="100px" alt="right Image">
                    <span class="nav-header"></span>
                </div>
                </td>
                    </tr>
                </table>
    
    
                <div class="info">
                    <table>
                        <tbody>
                            <tr>
                                <td><span class="label" for="course">Course: </span>
                                <b><span class="label" id="vcourse"><?php echo htmlspecialchars($row['course']); ?></span></b>
                            </td>
                                <td>
                                <span class="label" for="course"> Year Level: </span>
                                <b><span class="label" id="vyear"><?php echo htmlspecialchars($row['year_level']); ?></span></b><br>
                                </td>
                                <td>
                                <span class="label" for="course"> Section: </span>
                                <b><span class="label" id="vsection"><?php echo htmlspecialchars($row['section']); ?></span><br>
                                </td>
                                <td>
                                <span class="label" for="course">SY: <b>2024-2025</b></span>
                                </td>

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
                                        <b><span id="vlname"><?php echo htmlspecialchars($row['lname']); ?></span>
                                            <span>, </span>
                                            <span id="vfname"><?php echo htmlspecialchars($row['fname']); ?></span>
                                            <span> </span>
                                            <span id="vmname"><?php echo htmlspecialchars($row['mname']); ?></span>
                                            <span>.</span></b>
                                        </td>
                                        <td><span class="label" for="gender">Gender:</span></td>
                                        <td><b><span id="vgender"><?php echo htmlspecialchars($row['gender']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="religion">Religion:</span></td>
                                        <td><b><span id="vreligion"><?php echo htmlspecialchars($row['religion']); ?></span></b></td>
                                        <td><span class="label" for="address">Home Address:</span></td>
                                        <td><b><span id="vaddress"><?php echo htmlspecialchars($row['address']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="DateOfBirth">Date of Birth:</span></td>
                                        <td><b><span id="vbday"><?php echo htmlspecialchars($row['bday']); ?></span></b></td>
                                        <td><span class="label" for="parent">Parent / Guardian:</span></td>
                                        <td><b><span id="vguardian"><?php echo htmlspecialchars($row['guardian']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="age">Age:</span></td>
                                        <td><b><span id="vage"><?php echo htmlspecialchars($row['age']); ?></span></b></td>
                                        <td><span class="label" for="contact">Emergency Contact:</span></td>
                                        <td><b><span id="vemergency"><?php echo htmlspecialchars($row['emergency_no']); ?></span></b></td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                            </td>
                            </tr>
                      
                </table>
    
                <!-- Second row for Health Info, Physical Examination, and Lifestyle Info -->
                <table>
                    <tr>
                        <!-- Health Information -->
                        <td>
                            <div class=" section-title">Health Information</div>
                            <table class="b">
                                <tbody>
                                    <tr>
                                        <td><span class="label" for="allergies">Allergies:</span></td>
                                        <td><b><span id="vallergy"><?php echo htmlspecialchars($row['allergy']); ?></span></b></td>
                                        <td><span class="label" for="other">Other Health Condition:</span></td>
                                        <td><b><span id="vothers"><?php echo htmlspecialchars($row['others']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="asthma">Asthma:</span></td>
                                        <td><b><span id="vasthma"><?php echo htmlspecialchars($row['asthma']); ?></span></b></td>
                                        <td><span class="label" for="medication">Medication:</span></td>
                                        <td><b><span id="vmedication"><?php echo htmlspecialchars($row['medication']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="diabetes">Diabetes:</span></td>
                                        <td><b><span id="vdiabetes"><?php echo htmlspecialchars($row['diabetes']); ?></span></b></td>
                                        <td><span class="label" for="heartDisease">Heart Disease:</span></td>
                                        <td><b><span id="vheartdisease"><?php echo htmlspecialchars($row['heartdisease']); ?></span></b><br></td>
                                        
                                    </tr>
                                    <tr>
                                        
                                        <td><span class="label" for="vaccine">COVID Vaccine:</span></td>
                                        <td><b><span id="vcovidvax"><?php echo htmlspecialchars($row['vaccine']); ?></span></b></td>
                                        <td><span class="label" for="SeizureD">Seizure Disorder:</span></td>
                                        <td><b><span id="vseizure"><?php echo htmlspecialchars($row['seizure']); ?></span></b></td>
                                        
                                    </tr>
                                    <tr>
                                    <td><span class="label" for="vaccineStat">Vaccine Status:</span></td>
                                    <td><b><span id="vcvaxstatus"><?php echo htmlspecialchars($row['vaccine_status']); ?></span></b></td>
                                    </tr>    
                                </tbody>
                            </table>
                        </td>
                    
                        <!-- Divider between Health Info and Physical Examination -->
                        <td class="divider"></td>
                    
                        <!-- Physical Examination -->
                        <td class="physicalyexamlayout"  style="position: relative; top: -35px;">
                            <div class="section-title">Physical Examination</div>
                            <table class="b">
                                <tbody>
                                    <tr>
                                        <td><span class="label" for="exam_date">Date of Examination:</span></td>
                                        <td><b><span id="vdateOfexamination"><?php echo htmlspecialchars($row['dOexamination']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="height">Height:</span></td>
                                        <td><b><span id="vheight"><?php echo htmlspecialchars($row['height']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="weight">Weight:</span></td>
                                        <td><b><span id="vweight"><?php echo htmlspecialchars($row['weight']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="bp">Blood Pressure:</span></td>
                                        <td><b><span id="vbp"><?php echo htmlspecialchars($row['bloodpressure']); ?></span></b></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label" for="blood_type">Blood Type:</span></td>
                                        <td><b><span id="vbt"><?php echo htmlspecialchars($row['bloodtype']); ?></span></b></td>
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
                                        <td><b><span id="vsmoking"><?php echo htmlspecialchars($row['smoking']); ?></span></b></td>
                                        <td><span class="label">Liquor Drinking:</span></td>
                                        <td><b><span id="vliquordrinking"><?php echo htmlspecialchars($row['liquor']); ?></span></b></td>
                                    </tr>            
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            <div class="section-title" style="position: relative; top: -90px;">Medicine Prescription</div>
                <table class="personal-info-table" style="position: relative; top: -90px; border: 1px solid #ccc;" >
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
                <?php
               
                $historyId = $row['unique_id'];
                $historysql = "SELECT p.*, m.* FROM prescription_tbl p JOIN medicineinfo_tbl m ON p.med_id = m.med_id WHERE p.unique_id = ?";
                $historystmt = $conn->prepare($historysql);
                $historystmt->bind_param("i", $historyId);  // Assuming user_id is an integer (change "i" if it's another type)
                $historystmt->execute();
                  // Display the list of users
                  $history = $historystmt->get_result();
                  if ($history->num_rows > 0) {
  
                    while ($historyrow = $history->fetch_assoc()) {
                        
                        echo "<tr>";
                        echo "<td data-lable='ID no.'>" . $historyrow['med_name'] . "</td>";
                        echo "<td data-lable='Name'>" . $historyrow['dosage'] . "</td>";
                        echo "<td data-lable='ID no.'>" . $historyrow['quantity'] . "</td>";
                        echo "<td data-lable='Name'>" . $historyrow['complaint'] . "</td>";
                        echo "<td data-lable='Age'>" . $historyrow['dOprescription'] . "</td>";
                        echo "</tr>";
                    }
                    } else {
                        echo'<div style="color: RED; text-align:center; position: relative; top: 30px; font-weight: 700">No record found.</div>';
                    }
                
                  ?> 
                  </tbody>
            </table>

            </div>
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
                                $result_certified = $stmt->get_result();
    
                                // Fetch the single row where user_id matches
                                $row_certified = $result_certified->fetch_assoc(); // Fetch as an associative array
    
                                // Check if any result is returned
                                if ($row_certified) {
                                    // Access the row data here, e.g.:
                                    echo htmlspecialchars($row_certified['lname']) . ", " . htmlspecialchars($row_certified['fname']);
                                    echo "<br><span id='vbt'>" . htmlspecialchars($row_certified['position']) . "</span><br>";
                                } else {
                                    echo "Administrator";
                                }
                            ?>
                        </b></p>
                        <br>
                        <br>
                        <br>
                    </span>
                    <div class="page-break"></div> 
                </div>
            <hr>
            <?php
        }
        echo "<script src='print.php' type='text/javascript'>
            printInvoice();
          </script>";
    } else {
        echo "No records found.";
    }
    ?>
    </div>
    <?php
}
?>
</html>