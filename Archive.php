<?php
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
      $stmt = $conn->prepare("SELECT account_tbl.*, user_tbl.* 
  FROM account_tbl 
  JOIN user_tbl ON account_tbl.user_id = user_tbl.user_id 
  WHERE (CONCAT(user_tbl.fname, ' ', user_tbl.lname) LIKE ? OR user_tbl.prc_id LIKE ?) AND (user_tbl.dateDeleted IS NOT NULL 
  AND user_tbl.dateDeleted != '0000-00-00')");
      $search_term = '%' . $search_query . '%';
      $stmt->bind_param("ss", $search_term, $search_term);
      $stmt->execute();
      return $stmt->get_result(); // Fetch the results from the query
  } else {
      // If no search term, show all records
      $sql = "SELECT account_tbl.*, user_tbl.* 
  FROM account_tbl 
  JOIN user_tbl ON account_tbl.user_id = user_tbl.user_id 
  WHERE user_tbl.dateDeleted IS NOT NULL 
  AND user_tbl.dateDeleted != '0000-00-00'";
      return $conn->query($sql); // Execute the non-prepared query for all records
  }

}
else {
  // If no search term, show all records
  $sql = "SELECT account_tbl.*, user_tbl.* 
  FROM account_tbl 
  JOIN user_tbl ON account_tbl.user_id = user_tbl.user_id 
  WHERE user_tbl.dateDeleted IS NOT NULL 
  AND user_tbl.dateDeleted != '0000-00-00'";
  return $conn->query($sql); // Execute the non-prepared query for all records
}
}
?>
<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="adminStyle.css" />
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
         <i class="fas fa-users-cog"></i>
          <span class="nav-item">Account</span>
        </a></li>
        <li><a href="Archive">
          <i id="active" class="fas fa-archive"></i>
          <span id="active" class="nav-item">Archive</span>
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
          <h2>Archive</h2>
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
        <th>Action</th>
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
      echo "<td>" . $row['fname'] . " " . $row['lname'] ."</td>";
      echo "<td>" . $row['prc_id'] . "</td>";
      echo "<td>" . $row['contact_no'] . "</td>";
      echo "<td>" . $row['position'] . "</td>";
      echo "<td>
      <form id='archiveForm_" . $row['user_id'] . "' method='post'>
          <input type='hidden' name='user_id' value='" . $row['user_id'] . "'>
          <button type='button' class='archivebtn' onclick='confirmArchive(". $row['user_id'] .")'>
              <i class='fas fa-archive'></i>
              <span class='button-text'>Retrieve</span>
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
</body>
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
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, retrieve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form corresponding to the user
            document.getElementById('archiveForm_' + userId).submit();
        }
    });
}

</script>
<?php 
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive'])){
    
  $user_id = $_POST['user_id'];
  $sql = "UPDATE user_tbl SET dateDeleted =  '0000-00-00' WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  
  if ($stmt->execute()) {
    $sql = "UPDATE account_tbl SET dateDeleted =  '0000-00-00' WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->execute()) {
      echo "<script>
          Swal.fire('Success', 'Retrieved successfully!', 'success').then(() => {
              window.location.replace('Archive');
          });
      </script>";
  } else {
      echo "<script>
          Swal.fire('Error', 'Error retrieving record!', 'error');
      </script>";
  }
  
  $stmt->close();

} else {
  echo "<script>
      Swal.fire('Error', 'Error retrieving record!', 'error');
  </script>"; 
}}
?>
</html>