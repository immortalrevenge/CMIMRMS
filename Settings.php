<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href="index";alert("Please Login");</script>';
    exit();
}
include 'conn.php';

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
?>
<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="userStyle.css" />
  <!-- Font Awesome Cdn Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="scriptpop.js"></script>
  <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
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
        <i id="active" class="fas fa-user-cog"></i>
        <span id="active" class="nav-item">Account Setting</span>
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
          <h2>Personal Information</h2>
          </div>
        </div>
        <section class="persoCard">
    <div class="persoInner">
        <div class="accountcontainer">
            <div class="settingsView">
                <?php
                // Display the list of users
                $result = fetchUsers($conn);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                <div class="profile-container">
                        <p class="persoInfoheader"><?php echo $row['fname'] . " " . $row['lname']?></p>
    
                        <div class="info-row">
                            <div class="info">
                            <label for="Username">Username:</label>
                            <span><?php echo $row['username']?></span>
                            </div>
                            <div class="info">
                                <label>Email:</label>
                                <span><?php echo $row['email']?></span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info">
                                <label>Position:</label>
                                <span><?php echo $row['position']?></span>
                            </div>
                            <div class="info">
                                <label>Contact Number:</label>
                                <span><?php echo $row['contact_no']?></span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info">
                                <label>PRC Number:</label>
                                <span><?php echo $row['prc_id']?></span>
                            </div>
                            <div class="info">
                                <!-- Empty column to keep the layout even -->
                            </div>
                        </div>
                        <div class="button-container">
                        <button type="button" class="acctUpdateBtn" data-modal-target='#updateAccForm' data-id="<?php echo $_SESSION['user_id']; ?>">UPDATE</button>
                        <button type="button" class="acctUpdateBtn" data-modal-target='#updatePassForm' data-id="<?php echo $_SESSION['user_id']; ?>">CHANGE PASSWORD</button>

                    </div>
                </div>
                
                <?php 
                    } 
                } else {
                    echo "No users found.";
                }
                ?>
            </div>
        </div>
    </div>
</section>

  </section>
</div>
<!-- Update Form -->
<div class="pmodal" id="updateAccForm">
        <div class="modal-body" >
        <div class="accformcontainer">
            <header>Account Settings</header>
            <form method="POST">
<div class="forms" id="form1">
    <span class="title">Personal Information</span>
      <input type="hidden" id="viewuser_id"name="user_id" required>
    <div class="fields">
        <div class="pinput-fields">
        <label for="PRC"><b>PRC Number</b></label>
        <input class="form-control" id="viewprc_id" type="number" name="prc_id" placeholder="PRC Number" required>
        </div>
        <div class="pinput-fields">
        <label for="FNAME"><b>First Name</b></label>
        <input class="form-control" id="viewfname" type="text" name="fname" placeholder="First Name" required>
        </div>

        <div class="pinput-fields">
        <label for="LNAME"><b>Last Name</b></label>
        <input class="form-control" id="viewlname" type="text" name="lname" placeholder="Last Name" required>
        </div>

        <div class="pinput-fields">
        <label for="MNAME"><b>Middle Name</b></label>
        <input class="form-control" id="viewmname" type="text" name="mname" placeholder="Middle Name" required>
        </div>

        <div class="pinput-fields">
        <label for="CP"><b>Contact Number</b></label>
        <input class="form-control" id="viewcontact_no" type="text" name="contact_no" placeholder="ex: 09********" required>
        </div>

        <div class="pinput-fields">
        <label for="EMAIL"><b>Email</b></label>
        <input class="form-control" id="viewemail" type="text" name="email" placeholder="example@email.com" required>
        </div>
        <div class="btn-box">
    <button type="submit" name="updatedata">Update</button>
</div>
    </div>  

</div>
</div>
    
            </form>
            
        </div>
    </div>
    </div>
<!-- Change password Form -->
<div class="pmodal" id="updatePassForm">
        <div class="modal-body" >
        <div class="presformcontainer">
            <header>Change Password</header>
            <form id="myForm" method="POST">
<div class="forms" id="form1">
        <span class="title">Change Passowrd</span>
        <input type="hidden" id="user_id"name="user_id"  value=<?php echo $_SESSION['user_id']?> required>
        <div class="fields">

        <div class="pinput-fields">
        <label for="Password"><b>New Password</b></label>
        <input class="form-control" id="viewpassword" type="password" name="password" placeholder="Password" required>
        </div>

        <div class="pinput-fields">
        <label for="Confirm"><b>Confirm Password</b></label>
        <input class="form-control" id="viewcpassword" type="password" name="cpassword" placeholder="Confirm" required>
        </div>  

<div class="btn-box">
    <button type="submit" name="updatePass">Update</button>
</div>
</div>
</div>
    
            </form>
            
        </div>
    </div>
    </div>        
    <div id="overlay"></div>
    <div id="toastBox"></div>
  <?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatedata'])) {
  $user_id = $_POST['user_id'];
  $prc_id = $_POST['prc_id'];
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $mname = $_POST['mname'];
  $contact_no = $_POST['contact_no'];
  $position = $_POST['position'];
  $email = $_POST['email'];

              // Transaction for updates
              $conn->begin_transaction();

              try {
                  // Update `user_tbl`
                  $sql1 = "UPDATE user_tbl SET 
                          prc_id = ?, 
                          fname = ?, 
                          lname = ?, 
                          mname = ?, 
                          contact_no = ?, 
                          position = ?
                          WHERE user_id = ?";
                  $stmt1 = $conn->prepare($sql1);
                  $stmt1->bind_param('isssssi', $prc_id, $fname, $lname, $mname, $contact_no, $position, $user_id);
                  $stmt1->execute();

                  // Commit transaction
                  $conn->commit();

                  echo "<script>
                        Swal.fire('Success', 'Account updated successfully!', 'success').then(() => {
                            window.location.replace('Settings');
                        });
                        </script>";
              } catch (Exception $e) {
                  // Rollback transaction in case of error
                  $conn->rollback();
                  echo "<script>
                        Swal.fire('Error', 'Error updating account: {$e->getMessage()}', 'error');
                        </script>";
              }   
      }
      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatePass'])) {
        $user_id = $_POST['user_id'];
        $pass = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
      
        if ($pass == $cpassword){
                    // Transaction for updates
                    $conn->begin_transaction();
      
                    try {
      
                        // Update `account_tbl`
                        $sql2 = "UPDATE account_tbl SET 
                                 password = ?
                                 WHERE user_id = ?";
                        $stmt2 = $conn->prepare($sql2);
                        $stmt2->bind_param('si', $password, $user_id);
                        $stmt2->execute();
      
                        // Commit transaction
                        $conn->commit();
      
                        echo "<script>
                              Swal.fire('Success', 'Password updated successfully!', 'success').then(() => {
                                  window.location.replace('Settings');
                              });
                              </script>";
                    } catch (Exception $e) {
                        // Rollback transaction in case of error
                        $conn->rollback();
                        echo "<script>
                              Swal.fire('Error', 'Error updating password: {$e->getMessage()}', 'error');
                              </script>";
                    }
                }else{ 
                    echo "<script>
                              Swal.fire('Error', 'Password does not match!', 'error');
                            </script>";
                  } 
            }  
   
            
function fetchUsers($conn) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT 
            u.*, a.*
        FROM 
            user_tbl u 
        JOIN 
            account_tbl a 
        ON 
            u.user_id = a.user_id 
        WHERE 
            u.user_id = ?";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();
return $result;
}
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