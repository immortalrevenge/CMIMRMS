<?php
session_start();
if (!isset($_SESSION['username'])) {
  echo '<script>window.location.href="index";alert("Please Login");</script>';
    exit();
}
include 'conn.php';

function fetchOrderTable($conn) {
  $sql = "SELECT * FROM order_tbl JOIN medicineinfo_tbl ON order_tbl.med_id = medicineinfo_tbl.med_id ORDER BY order_tbl.order_id DESC"; // Adjust your query accordingly
  return $conn->query($sql);
 }
 function fetchLogTable($conn) {
  $sql = "SELECT * FROM log_tbl JOIN account_tbl ON log_tbl.user_id = account_tbl.user_id ORDER BY log_tbl.log_id DESC";
  return $conn->query($sql);
 }

?>
<span style="font-family: verdana, geneva, sans-serif;">
  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="adminStyle.css" />
  <!-- Font Awesome Cdn Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
</head>
<body>
<div class="container">
  <nav>
      <ul>
      <li><div class="logo">
        <img src="images/clinicLogo.png" alt="neust logo">
          <span class="nav-header">Admin</span>
        </div></li>
        <li><a href="Administrator">
          <i id="active" class="fas fa-home"></i>
          <span id="active" class="nav-item">Dashboard</span>
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
        <div class="header--wrapper">
          <div class="header--title">
          <h2>Overview</h2>
          </div>
          
        </div>
      
        <div class="tableDashboard">
          <div class="dCard">
            <div>
              <h2>Medicine</h2>
              <!-- <a href="#" class="view"></a> -->
            </div>
            <div class="table-scroll">
            <table class="tbl">
                <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Date of Purchase</th>
                    <th>Date of Expiration</th>
                    <th>Quantity</th>
                </tr>
                </thead>  
                <tbody>
                <?php
// Display the list of users
$result = fetchOrderTable($conn);
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $userId = $row['order_id'];
      echo "<tr>";
      echo "<td>" . $row['med_name'] . "</td>";
      echo "<td>" . $row['acquisition'] . "</td>";
      echo "<td>" . $row['expiration'] . "</td>";
      echo "<td>" . $row['quantity'] . "</td>";
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

          <div class="dCard">
            <div>
              <h2>Recent Logs</h2>
              <!-- <a href="#" class="view"></a> -->
            </div>

            <div class="table-scroll">
            <table class="tbl">
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Action</th>
                    <th>Date</th>
                </tr>
                </thead>  
                <tbody>
                <?php
// Display the list of users
$result = fetchLogTable($conn);
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $userId = $row['log_id'];
      echo "<tr>";
      echo "<td>" . $row['username'] . "</td>";
      echo "<td>" . $row['action'] . "</td>";
      echo "<td>" . $row['timestamp'] . "</td>";
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
</div>
    </section>
    </div>     
</body>
</html>