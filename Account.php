<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
session_start();
if (!isset($_SESSION['username'])) {
  echo '<script>window.location.href="index";alert("Please Login");</script>';
  exit();
}
include 'conn.php';
$search_query = '';
function fetchUsers($conn, $search_query = null) {
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
      $search_query = $_POST['search'];
  
  if ($search_query !== null && $search_query != "") {
      // If there's a search term, use it in the WHERE clause
      $stmt = $conn->prepare("SELECT * 
FROM user_tbl 
WHERE (CONCAT(fname, ' ', lname) LIKE ? OR prc_id LIKE ?)
AND (dateDeleted IS NULL OR dateDeleted = '')");
      $search_term = '%' . $search_query . '%';
      $stmt->bind_param("ss", $search_term, $search_term);
      $stmt->execute();
      return $stmt->get_result(); // Fetch the results from the query
  } else {
      // If no search term, show all records
      $sql = "SELECT * FROM user_tbl WHERE dateDeleted = NULL || dateDeleted = ''";
      return $conn->query($sql); // Execute the non-prepared query for all records
  }

}
else {
  // If no search term, show all records
  $sql = "SELECT * FROM user_tbl WHERE dateDeleted = NULL || dateDeleted = ''";
  return $conn->query($sql); // Execute the non-prepared query for all records
}
}
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
?>

<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="adminStyle.css" />
  <script defer src="scriptpop.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <!-- Font Awesome Cdn Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
</head>
<body>
  <div class="container">
  <nav>
      <ul>
      <li>
        <div class="logo">
          <img src="images/clinicLogo.png" alt="neust logo">
          <div>
            <span class="nav-header">Admin</span><br>
          </div>
        </div>
      </li>
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
        <i class="fas fa-chart-line"></i>
          <span class="nav-item">Report</span>
        </a></li>
        <li><a href="Account">
         <i id="active" class="fas fa-users-cog"></i>
          <span id="active" class="nav-item">Account</span>
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
        <div class="header--wrapper">
          <div class="header--title">
          <h2>Clinic Account</h2>
          </div>
         
        </div>
        <section class="tableM">

        <div class="table-list">
        <div class="header-container">
        <div class="search--box">
        <form id="searchForm" method="POST" action="">
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Search by ID, Name" id="searchInput" value="<?php echo $search_query; ?>"/>
        </form>
            </div>
      <div class="tooltip">
      <button button type="button" class="addBtn" data-modal-target='#registerForm'>
          <i class="fas fa-plus" id="plus"></i>Register
        </button>
        <span class="tooltiptext">add new user</span>
      </div>
      
    </div>
        <div class="responsive-tbl">
        <div class="tbl_container">
            <table class="tbl">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>PRC No.</th>
                    <th>Contact No.</th>
                    <th>Position</th>
                    <th colspan="2">Action</th>
                </tr>
                </thead>  
                <tbody>
                <?php
// Display the list of users
$result = fetchUsers($conn);
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $userId = $row['user_id'];
      echo "<tr>";
      echo "<td>" . $row['fname'] . " " . $row['lname'] . "</td>";
      echo "<td>" . $row['prc_id'] . "</td>";
      echo "<td>" . $row['contact_no'] . "</td>";
      echo "<td>" . $row['position'] . "</td>";
      
      // Edit Button to trigger the modal
      echo "<td>";
      echo "<button type='button' class='editbtn' data-modal-target='#updateAccForm' data-id='$userId'>
            <i class='fas fa-edit'></i><span class='button-text'>Edit</span></button>";
      echo "</td>";
      
      // Form and Button for Archiving
      echo "<td>
      <form id='archiveForm_" . $row['user_id'] . "' method='post'>
          <input type='hidden' name='user_id' value='" . $row['user_id'] . "'>
          <button type='button' class='archivebtn' onclick='confirmArchive(". $row['user_id'] .")'>
              <i class='fas fa-archive'></i><span class='button-text'>Archive</span>
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
  </section>
  </section>
  </div>
<!-- Registration User Form -->
<div class="modal" id="registerForm">
    <div class="modal-body" >
        <div class="formcontainer">
            <header>Registration</header>
            <form method="post"  id="register">
                <div class="forms">
                    <span class="title">Personal Information</span> 
                    
                    <div class="fields">
                        
                        <div class="input-fields">
                          <label><b>PRC Number</b></label>
                          <input type="number" name="prc_id" placeholder="PRC Number" required>
                        </div>
                        <div class="input-fields">
                          <label><b>Last Name</b></label>
                          <input type="text" name="lname" placeholder="Last Name" required>
                        </div>

                        <div class="input-fields">
                          <label><b>First Name</b></label>
                          <input  type="text" name="fname" placeholder="First Name" required>
                        </div>

                        <div class="input-fields">
                          <label><b>Middle Name</b></label>
                          <input  type="text" name="mname" placeholder="Middle Name" required>
                        </div>
                        <div class="input-fields">
                            <label>Gender</label>
                            <input  type="text" name="gender" placeholder="Enter your Gender" required>
                        </div>
                        <div class="input-fields">
                            <label>Date of Birth</label>
                            <input  type="date" name="bday" placeholder="Enter your Date of Birth" required>
                        </div>

                        <div class="input-fields">
                          <label><b>Contact Number</b></label>
                          <input  type="text" name="contact_no" placeholder="ex: 09********" required>
                        </div>
                        <div class="input-fields">
                            <label>Position</label>
                            <select class="fill" name="position" required><br>
                                <option class="fill" value="University Physician">University Physician</option>
                                <option class="fill" value="Nurse III">Nurse III</option>
                                <option class="fill" value="Nurse II">Nurse II</option>
                                <option class="fill" value="Nurse I">Nurse I</option>
                                <option class="fill" value="Nursing Aide">Nursing Aide</option>
                                <option class="fill" value="Job Order Personnel">Job Order Personnel</option>
                            </select>
                        </div>
                        

                        <div class="input-fields">
                            <label><b>Email</b></label>
                            <input type="email" name="email" placeholder="example@email.com" required>
                        </div>

                        <div class="input-fields">
                          <label><b>Username</b></label>
                          <input  type="text" name="username" placeholder="Username" required>
                        </div>

                        <div class="input-fields">
                          <label><b>Password</b></label>
                          <input type="password" id="output" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="btn-box">
                        <button class="submitBtn" type="submit"  name="registeruser" onclick="generate_password()" >Submit</button>
                         <script src="generatepassword.js"></script>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    </div>
    <!-- Update Form -->
<div class="modal acctModal" id="updateAccForm">
        <div class="modal-body" >
        <div class="formcontainer">
            <header>Account Settings</header>
            <form id="myForm" method="POST">
<div class="forms" id="form1">
    <span class="title">Personal Information</span>
      <input type="hidden" id="viewuser_id"name=" user_id" required>
    <div class="fields">
        <div class="input-fields">
        <label for="PRC"><b>PRC Number</b></label>
        <input class="form-control" id="viewprc_id" type="number" name="prc_id" placeholder="PRC Number" required>
        </div>
        <div class="input-fields">
        <label for="FNAME"><b>First Name</b></label>
        <input class="form-control" id="viewfname" type="text" name="fname" placeholder="First Name" required>
        </div>

        <div class="input-fields">
        <label for="LNAME"><b>Last Name</b></label>
        <input class="form-control" id="viewlname" type="text" name="lname" placeholder="Last Name" required>
        </div>

        <div class="input-fields">
        <label for="MNAME"><b>Middle Name</b></label>
        <input class="form-control" id="viewmname" type="text" name="mname" placeholder="Middle Name" required>
        </div>

        <div class="input-fields">
        <label for="CP"><b>Contact Number</b></label>
        <input class="form-control" id="viewcontact_no" type="text" name="contact_no" placeholder="ex: 09********" required>
        </div>

        <div class="input-fields">
                            <label><b>Position</b></label>
                            <select class="fill" id="viewposition" name="position" required><br>
                                <option class="fill" value="University Physician">University Physician</option>
                                <option class="fill" value="Nurse III">Nurse III</option>
                                <option class="fill" value="Nurse II">Nurse II</option>
                                <option class="fill" value="Nurse I">Nurse I</option>
                                <option class="fill" value="Nursing Aide">Nursing Aide</option>
                                <option class="fill" value="Job Order Personnel">Job Order Personnel</option>
                            </select>
                        </div>
        <div class="btn-box">
    <button class="submitBtn" type="submit" name="updatedata">Update</button>
</div>
    </div>  

</div>
</div>
    
            </form>
            
        </div>
    </div>
    
    <div id="overlay"></div>
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
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form corresponding to the user
            document.getElementById('archiveForm_' + userId).submit();
        }
    });
}

</script>
<?php
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registeruser'])){
    
  $prc_id = $_POST['prc_id'];
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $mname = $_POST['mname'];
  $gender = $_POST['gender'];
  $bday = $_POST['bday'];
  $contact_no = $_POST['contact_no'];
  $position = $_POST['position'];
  $email = $_POST['email'];
  $username = $_POST['username'];
  $pass = $_POST['password'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // chechk if the username is available


  $checkusername = mysqli_query($conn, "SELECT * FROM account_tbl WHERE username = '$username' LIMIT 1");
  $count = mysqli_num_rows($checkusername);
  
  if($count == 1){
      // username already exist
      echo "<script>
            Swal.fire('Error', 'Username already exists!', 'error');
          </script>";
      
  } else {
      $checkprc = mysqli_query($conn, "SELECT * FROM user_tbl WHERE prc_id = '$prc_id' LIMIT 1");
      $count2 = mysqli_num_rows($checkprc);
      if($count2 == 1){
        echo "<script>
            Swal.fire('Error', 'PRC Number already exist!', 'error');
          </script>";
      } else {
          $sql2 = "INSERT INTO user_tbl (prc_id, fname, lname, mname, gender, bday, contact_no, position) VALUES (?, ?,?, ?, ?, ?, ?, ?)";
          $stmt2 = $conn->prepare($sql2);
          $stmt2->bind_param("isssssss", $prc_id, $fname, $lname , $mname,$gender, $bday, $contact_no, $position);
          $stmt2->execute();
          $last_user_id = $conn->insert_id;
  
          $sql = "INSERT INTO account_tbl (user_id, username, password, email) VALUES (?,?,?,?)";
          $stmtinsert = $conn->prepare($sql);
          $stmtinsert->bind_param("isss", $last_user_id, $username,$password, $email);
          $result = $stmtinsert->execute();
          $conn->commit();
          if($result){
            
              $greeting = "Congratulations, and welcome to Clinic Portal! Your account has been successfully verified and is now ready for use. The details below is your new Username and Password";
              $link = "http://localhost/clin/index.php";
              $tag1 = "Username: ";
              $tag2 = "Password: ";
              $message = $greeting . "<br>" .$link  . "<br>" .  $tag1 . $username . "<br>" . $tag2 . $pass;
              
              // Create a PHPMailer object
              $mail = new PHPMailer(true);
      
              try {
                      $mail->isSMTP();
                      $mail->Host       = 'smtp.gmail.com';
                      $mail->SMTPAuth   = true;
                      $mail->Username   = 'clinicportal2024@gmail.com';
                      $mail->Password   = 'iqeq uauy xsqg rada';
                      $mail->SMTPSecure = 'tls';
                      $mail->Port       = 587;
      
                      $mail->setFrom('clinicportal2024@gmail.com', 'Clinic Portal');
                              $mail->addAddress($emailW); // Use the email from your form
      
                              // Content
                              $mail->isHTML(true);
                              $mail->Subject = "Welcome to Clinic Portal! Your Account Has Been Successfully Verified";
                              $mail->Body    = $message;
      
                              $mail->send();
                              echo "<script>
            Swal.fire('Success', 'Registered User successfully!', 'success').then(() => {
                window.location.replace = ('Account');
            });
          </script>";
                  
                  // echo '<script>window.location="admin.php";</script>';
              } catch (Exception $e) {
                  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
              }
          
            
              
          }else{ 
            echo "<script>
            Swal.fire('Error', 'Error registering user!', 'error');
          </script>";
             
          } 
      }
  }  


}
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatedata'])) {
  
  // Get form data
  $user_id = $_POST['user_id']; // Assuming you are passing user_id for the update
  $prc_id = $_POST['prc_id'];
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $mname = $_POST['mname'];
  $gender = $_POST['gender'];
  $bday = $_POST['bday'];
  $contact_no = $_POST['contact_no'];
  $position = $_POST['position'];

  // Begin transaction
  $conn->begin_transaction();

  try {
      // Update user information in `user_tbl`
      $sql1 = "UPDATE user_tbl 
              SET prc_id = ?, fname = ?, lname = ?, mname = ?, gender = ?, bday = ?, contact_no = ?, position = ?
              WHERE user_id = ?";
      $stmt1 = $conn->prepare($sql1);
      $stmt1->bind_param("isssssssi", $prc_id, $fname, $lname, $mname, $gender, $bday, $contact_no, $position, $user_id);
      $stmt1->execute();
      // Commit transaction
      $conn->commit();

      // Success message
      echo "<script>
            Swal.fire('Success', 'User updated successfully!', 'success').then(() => {
                window.location.replace('Account');
            });
          </script>";

  } catch (Exception $e) {
      // Rollback in case of an error
      $conn->rollback();
      echo "<script>
            Swal.fire('Error', 'Error updating user: {$e->getMessage()}', 'error');
          </script>";
  }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive'])){
    
  $user_id = $_POST['user_id'];
  $sql = "UPDATE user_tbl SET dateDeleted =  NOW() WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  
  if ($stmt->execute()) {
    $sql = "UPDATE account_tbl SET dateDeleted =  NOW() WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->execute()) {
      echo "<script>
          Swal.fire('Success', 'Archived successfully!', 'success').then(() => {
              window.location.replace('Account');
          });
      </script>";
  } else {
      echo "<script>
          Swal.fire('Error', 'Error archiving record!', 'error');
      </script>";
  }
  
  $stmt->close();

} else {
  echo "<script>
      Swal.fire('Error', 'Error archiving record!', 'error');
  </script>"; 
}}
function fetchUserById($conn, $userId) {
  $userId = ($userId); // Sanitize the input to prevent SQL injection
  $sql = "SELECT 
          u.*, a.*
      FROM 
          user_tbl u 
      JOIN 
          account_tbl a 
      ON 
          u.user_id = a.user_id 
      WHERE 
          u.user_id = $userId";
  return $conn->query($sql);
}
?>
</body>
</html>