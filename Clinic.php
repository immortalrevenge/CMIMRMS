<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href="index";alert("Please Login");</script>';
    exit();

}
include 'conn.php';
$search_query = "";
function fetchUsers($conn, $search_query = null) {

  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
      $search_query = $_POST['search'];
  
  if ($search_query !== null && $search_query != "") {
      // If there's a search term, use it in the WHERE clause
      $stmt = $conn->prepare("SELECT 
  i.*,
  m.*
FROM 
  inventory_tbl i
JOIN 
  medicineinfo_tbl m ON m.med_id = i.med_id
WHERE 
  i.date_created = (
      SELECT 
          MAX(date_created) 
      FROM 
          inventory_tbl 
      WHERE 
          med_id = i.med_id && m.med_name LIKE ?
  )
ORDER BY 
  i.med_id ASC LIMIT 10;");
      $search_term = '%' . $search_query . '%';
      $stmt->bind_param("s", $search_term);
      $stmt->execute();
      return $stmt->get_result(); // Fetch the results from the query
  } else {
      // If no search term, show all records
      $sql ="SELECT 
  i.med_id,
  i.stock,
  i.date_created,
  m.*
FROM 
  inventory_tbl i
JOIN 
  medicineinfo_tbl m ON m.med_id = i.med_id
WHERE 
  i.date_created = (
      SELECT 
          MAX(date_created) 
      FROM 
          inventory_tbl 
      WHERE 
          med_id = i.med_id
  )
ORDER BY 
  i.med_id ASC LIMIT 10;";
      return $conn->query($sql); // Execute the non-prepared query for all records
  }

}
else {
  // If no search term, show all records
  $sql ="SELECT 
  i.med_id,
  i.stock,
  i.date_created,
  m.*
FROM 
  inventory_tbl i
JOIN 
  medicineinfo_tbl m ON m.med_id = i.med_id
WHERE 
  i.date_created = (
      SELECT 
          MAX(date_created) 
      FROM 
          inventory_tbl 
      WHERE 
          med_id = i.med_id
  )
ORDER BY 
  m.med_name ASC;";
  return $conn->query($sql); // Execute the non-prepared query for all records
}
}

$Wcasesql = "SELECT dOprescription, complaint AS cases, COUNT(*) AS no_cases 
FROM prescription_tbl WHERE YEARWEEK(dOprescription) = YEARWEEK(NOW())
GROUP BY complaint;";
$stmt = $conn->prepare($Wcasesql);
$stmt->execute();
$result = $stmt->get_result();

$Wno_cases = [];
$Wcases = [];

while ($row = $result->fetch_assoc()) {
    $Wcases[] = $row['cases'];
    $Wno_cases[] = $row['no_cases'];
}

$stmt->close();
?>
<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="userStyle.css" />
  
  <script defer src="scriptpop.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
          <i id="active" class="fas fa-home"></i>
          <span  id="active" class="nav-item">Dashboard</span>
        </a></li>
        <li><a href="Medical Record">
          <i class="fas fa-notes-medical"></i>
          <span class="nav-item">Medical Record</span>
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
          <h2>Dashboard</h2>
          </div>
          <div class="user--info">
            
            <h4><?php echo $_SESSION['username']?></h4>
          </div>
        </div>
        <div class="main-skills">
        <div class="card">
        <div class="icon">
        <i class="fas fa-user-friends"></i>
         </div>
        <h5>Total Student Record 
        <div class="tooltip">
        <span class="tooltiptext">Register New Record</span>
            <button class="addBtn" data-modal-target='#addform'>
          <i class="fas fa-plus" id="plus"></i>Register
          </div>
        </button></h5>
          <h1><?php 
          $sql = "SELECT COUNT(id_no) AS total_records FROM personalinfo_tbl WHERE position = 'student' AND archive_status != 'archived'";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $total_records = $row["total_records"];
              echo "<p>$total_records</p>";
          } else {
              echo "0 results";
          }
          $result->close();
          ?></h1>
        </div>
        <div class="card">
          <div class="icon">
        <i class="fas fa-users"></i>
         </div>
          <h5>Total Faculty & Staff 
          <div class="tooltip">
          <span class="tooltiptext">Register New Record</span>
          <button class="addBtn" data-modal-target='#addform'>
          <i class="fas fa-plus" id="plus"></i>Register
          </div>
        </button></h5>
          <h1><?php 
          $sql = "SELECT COUNT(id_no) AS total_records FROM personalinfo_tbl WHERE position = 'faculty' || id_no = NULL";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $total_records = $row["total_records"];
              echo "<p>$total_records</p>";
          } else {
              echo "0 results";
          }
          $result->close();
          ?></h1>
        </div>
        <div class="card">
        <div class="icon">
        <i class="fas fa-clinic-medical"></i>
         </div>
         <h5>Visiting Patient
         <div class="tooltip">
         <span class="tooltiptext">Prescribe Medicine</span>       
          <button class='prescribebtn' data-modal-target='#prescribeForm'> <i class='fas fa-prescription'></i>
          Prescribe </button>
          </div>
          </h5>
          <h1><?php 
          $sql = "SELECT COUNT(*) AS total_records FROM prescription_tbl";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $total_records = $row["total_records"];
              echo "<p>$total_records</p>";
          } else {
              echo "0 results";
          }
          $result->close();
          ?></h1>
        </div>
       </div>
       <div class="tableD">
          <div class="chartD">
            <div class="row">
            <div style="width: 80%; margin: 0 auto;">
        <h2>Weekly Health Cases <?php echo date('F Y'); ?></h2>
        <canvas id="WcasesChart" style="display: block;box-sizing: border-box;height: 195px;width: 338px;"></canvas>
    </div>

    <script>
        var ctx = document.getElementById('WcasesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'bar', // You can change this to 'bar', 'pie', etc.
            data: {
                labels: <?php echo json_encode($Wcases); ?>, // Days of the month
                datasets: [{
                    label: [],
                    data: <?php echo json_encode($Wno_cases); ?>,
                    backgroundColor: '#fdcb0e',
                    borderColor: '#fdcb0e',
                    borderWidth: 1,
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
          </div>
          
          <div class="chartD">
             
              <h2>Medicine Status</h2>
            <!-- <a class="md" href="#">View</a> -->
         
            <?php

$query = "SELECT 
    i.med_id,
    i.stock,
    i.date_created,
    m.* 
FROM 
    inventory_tbl i 
JOIN 
    medicineinfo_tbl m ON m.med_id = i.med_id 
WHERE 
    i.date_created = (
        SELECT 
            MAX(date_created) 
        FROM 
            inventory_tbl 
        WHERE 
            med_id = i.med_id
    ) AND i.stock <= 10 
ORDER BY 
    i.stock DESC 
LIMIT 10";

$query_run = mysqli_query($conn, $query);
?>

<table id="datatableid" class="medicine-table">
    <thead>
        <tr>
            <th scope="col">Medicine Name</th>
            <th scope="col">Category</th>
            <th scope="col">Type</th>
            <th scope="col">Stock</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($query_run && mysqli_num_rows($query_run) > 0) {
        foreach ($query_run as $row) {
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['med_name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['stock']); ?></td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="4" style="color: RED; text-align:center; font-weight: 700">No Low in Stock</td></tr>';
    }
    ?>
    </tbody>
</table>

       
        
        </div>
        </div>
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
        <!-- Prescription Form -->
        <div class="pmodal" id="prescribeForm" >
        <div class="modal-body" >
        <div class="presformcontainer">
            <header>Medicine Prescription</header>
            <form id="myForm" method="POST">
                <div class="forms" id="form1">
                    <input type="hidden" name="unique_id">
                    <div class="fieldsMarginTop">
                    <div class="pinput-fields dropdownMed">
    <label for="medicine">Medicine</label>
    <?php
                    // Generate the medicine dropdown
                    echo generateMedicineDropdown($conn, $search_query);
                ?>
</div>
                        <div class="pinput-fields">
                            <label for="">ID Number</label>
                            <input id="1stform" type="text" name="id_no" placeholder="Enter your ID Number" required>
                        </div>
                        <div class="pinput-fields">
                            <label for="">Quantity</label>
                            <input id="1stform" type="number" name="quantity" placeholder="Enter the Quantity" required>
                        </div>

                        <div class="pinput-fields">
                            <label for="">Chief of Complaint</label>
                            <select class="fill" name="complaint" ><br>
                            <option value="" disabled selected>Select Case</option>
          
                            <?php
                                         // Query to fetch distinct blood types from the database
                                $query = "SELECT * FROM cases_tbl";
                                $result = $conn->query($query);

                                if ($result->num_rows > 0) {
                                    // Loop through the result set and output each distinct blood type in an option element
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='". htmlspecialchars($row["case_name"]) . "'>" . htmlspecialchars($row["case_name"]) . "</option>";
                                    }
                                } 
                                        ?>
                                    </select>
                                      
                        </div>

                    </div>
                
                    <div class="btn-box">
                        <button type="submit" class="submit" name="prescribe_medicine" id="submit">Submit</button>
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
                return false;
              } else {
                nextForm();
                return true; 
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
</body>
<?php
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertdata']))
{
        $position = $_POST["position"];
          $id_no = $_POST["id_no"];
          $lname = $_POST["lname"];
          $fname = $_POST["fname"];
          $mname = $_POST["mname"];
          $bday = $_POST["bday"];
          $gender = $_POST["gender"];
          $religion = $_POST["religion"];
          // Convert the birth date string into a DateTime object
        $birthdate_obj = new DateTime($bday);

        // Get the current date
        $current_date = new DateTime();

        // Calculate the difference between the current date and the birth date
        $age_interval = $current_date->diff($birthdate_obj);
        $age = $age_interval->y;
          $course = $_POST["course"];
          $year = $_POST["year"];
          $section = $_POST["section"];
          $contact_no = $_POST["contact_no"];
          $address = $_POST["address"];
          $guardian = $_POST["guardian"];
          $emergency = $_POST["emergency"];
          $allergy = $_POST["allergy"];
          $asthma = $_POST["asthma"];
          $diabetes = $_POST["diabetes"];
          $heartdisease = $_POST["heartdisease"];
          $seizure = $_POST["seizure"];
          $others = $_POST["others"];
          $medication = $_POST["medication"];
          $covidvax = $_POST["covidvax"];
          // Check which checkboxes were checked
          $vaccineStatus = $_POST["vacStat"];
            $dateOfexamination = $_POST["dateOfexamination"];
            $height = $_POST["height"];
            $weight = $_POST["weight"];
            $bp = $_POST["bp"];
            $bt = $_POST["bt"];
            $bmi_category = $_POST["bmi"];
          $smoking = $_POST["smoking"];
          $liquordrinking = $_POST["liquordrinking"];
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
                                window.location.replace('Clinic');
                                });
            
                  </script>";
      } else {
        echo "<script>Swal.fire('Error', 'Failed to register the user. Please try again.', 'error');</script>";
    
      }
          } 
          if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["prescribe_medicine"])) {
            // Sample form inputs
            $idno = $_POST['id_no'];
            $med_name = $_POST['med_name'];
            $username = $_SESSION['username'];
            $quantity = $_POST['quantity'];
            $complaint = $_POST['complaint'];
        
            // Check if the id_no exists in the personalinfo_tbl table
            $sql_check_idno = "SELECT COUNT(*) as num_rows FROM personalinfo_tbl WHERE id_no = ?";
            $stmt_check_idno = $conn->prepare($sql_check_idno);
            $stmt_check_idno->bind_param("s", $idno);
            $stmt_check_idno->execute();
            $result_check_idno = $stmt_check_idno->get_result();
            $num_rows = $result_check_idno->fetch_assoc()['num_rows'];
    
            if ($num_rows == 0) {
                echo "<script>Swal.fire('Error', 'ID No. does not exist. Please register the patient first.', 'error');</script>";
                exit;
            }
            if ($quantity > 5) {
                echo "<script>
                    Swal.fire('Error', 'Patient cannot receive more than 6 medicine per prescription.', 'error');
                </script>";
                exit;
            }
                    // Find med_id from medicines table
                    $sql_medicine = "SELECT med_id FROM medicineinfo_tbl WHERE med_name = ?";
                    $stmt_medicine = $conn->prepare($sql_medicine);
                    $stmt_medicine->bind_param("s", $med_name);
                    $stmt_medicine->execute();
                    $result_medicine = $stmt_medicine->get_result();
                    $med_id = $result_medicine->fetch_assoc()['med_id'];
                    
            $sql_check = "SELECT * FROM inventory_tbl WHERE med_id = ? ORDER BY date_created DESC LIMIT 1";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $med_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows > 0) {
                // Product exists, so update the stock
                $row = $result->fetch_assoc();
                $new_stock = $row['stock'] - $quantity;
            
                if ($new_stock < 0) {
                    echo "<script>
                              Swal.fire('Error', 'Not enough stock available.', 'error').then(() => {
                               window.location.replace('Inventory');
                          });
                      </script>";
                    exit;
                }
            // Find patient_id from patients table
            $sql_patient = "SELECT unique_id FROM personalinfo_tbl WHERE id_no = ?";
            $stmt_patient = $conn->prepare($sql_patient);
            $stmt_patient->bind_param("s", $idno);
            $stmt_patient->execute();
            $result_patient = $stmt_patient->get_result();
            $unique_id = $result_patient->fetch_assoc()['unique_id'];
        
    
        
            $user_id = $_SESSION['user_id'];
            // Get the current date and the date one week ago
    $current_date = date("Y-m-d");
    $week_ago_date = date("Y-m-d", strtotime("-1 week"));
    
    // Check if the student has already reached the maximum allowed prescriptions in the last 7 days
    $sql_check_prescriptions = "SELECT COUNT(*) as num_prescriptions 
                                FROM prescription_tbl 
                                WHERE unique_id = ? AND dOprescription BETWEEN ? AND ?";
    $stmt_check_prescriptions = $conn->prepare($sql_check_prescriptions);
    $stmt_check_prescriptions->bind_param("iss", $unique_id, $week_ago_date, $current_date);
    $stmt_check_prescriptions->execute();
    $result_check_prescriptions = $stmt_check_prescriptions->get_result();
    $num_prescriptions = $result_check_prescriptions->fetch_assoc()['num_prescriptions'];
    
    if ($num_prescriptions > 5) {
        echo "<script>
        Swal.fire('Error', 'Patient has already reached the maximum allowed prescriptions for this week.', 'error');
      </script>";
        exit;
    } else {
    // Update Stock
    
    
        $sql_update = "INSERT INTO inventory_tbl (med_id, stock, date_created) VALUES (?, ?, NOW())";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $med_id, $new_stock);
        $stmt_update->execute();
    
    // Insert data into the prescriptions table
    $sql_insert = "INSERT INTO prescription_tbl (user_id, med_id, unique_id, quantity, complaint, dOprescription) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiiis", $user_id, $med_id, $unique_id, $quantity, $complaint);
    
    if ($stmt_insert->execute()) {
        $med_name = $_POST['med_name'];
        $action = "Prescribed Medicine: " . $med_name . " Quantity: " . $quantity . " New Stock: " . $new_stock;
        $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $action);
        $stmt->execute();
        echo "<script>
                      Swal.fire('Success', 'Prescription added successfully!', 'success').then(() => {
                       window.location.replace('Clinic');
                  });
              </script>";
    } else {
    echo "<script>
            Swal.fire('Error', 'Failed to record the prescription.', 'error');
              </script>";
    }
    }    
    }
    
    
    }
    function generateMedicineDropdown($conn, $search_query) {
        $output = '<select name="med_name" id="medicine" class="searchable-select" required>';
        $output .= '<option value="" disabled selected>Select Medicine</option>';
    
        $result = fetchUsers($conn, $search_query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $output .= "<option value='" . $row['med_name'] . "'>" . $row['med_name'] . " " . $row['dosage'] . "</option>";
            }
        } else {
            $output .= "<option value='' disabled>No medicine available.</option>";
        }
        
        $output .= '</select>';
        return $output;
    }
?>
</html>