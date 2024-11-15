
<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo '<script>window.location.href="index";alert("Please Login");</script>';
    exit();
}
include 'conn.php';
$search_query = "";
$today = date("Y-m-d");
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
    i.med_id;");
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
    i.med_id DESC LIMIT 10;";
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

?>
<span style="font-family: verdana, geneva, sans-serif;"><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  <link rel="stylesheet" href="userStyle.css" />
  <script defer src="scriptpop.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <!-- Include select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />

<!-- Include select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <script>
function setMedicineValue(input) {
    const selectedOption = document.querySelector(`option[value="${input.value}"]`);
    if (selectedOption) {
        const dosage = selectedOption.getAttribute('data-dosage');
        console.log(`Selected Medicine: ${input.value}, Dosage: ${dosage}`); // Debugging output
        document.getElementById('dosage').value = dosage;
    } else {
        console.log('No matching medicine found.'); // Debugging output
        document.getElementById('dosage').value = '';
    }
}
    </script>
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
          <i  id="active" class="fas fa-medkit"></i>
          <span  id="active" class="nav-item">Medicine Inventory</span>
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
          <h2>Medicine Inventory</h2>
          </div>
          <div class="user--info">
          <h4><?php echo $_SESSION['username']?></h4>
          </div>
        </div>

        
        <section class="tableM">
        <div class="table-list">
        <div class="header-container">
          
        <div class="search--box">
        <form method="post">
            <i class="fas fa-search" id="responSearch"></i>
            <input type="text" name="search" list="medicine" autocomplete="off"s placeholder="Search by Medicine" value="<?php echo $search_query; ?>">
            <!-- <button type="submit">Search</button> -->
            </form>
            </div>
            <div>
            <div class="tooltip">
      <span class="tooltiptext">Register New Record</span>
      <button class="addBtn" data-modal-target='#addform'>
          <i class="fas fa-plus" id="plus"></i><span class="responText">Register</span>
        </button>
      </div>
  </div>
      
      
    </div>
        <div class="responsive-tbl">
        <div class="tbl_container">
        <div class="user--info">
         
          </div>
          <div class="table-scroll">
            <table class="tbl" id="tbl">
                <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Dosage</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
                </thead>  
                <tbody>
                <?php
                  // Display the list of users
                  $result = fetchUsers($conn, $search_query);
                  if ($result->num_rows > 0) {
  
                    while ($row = $result->fetch_assoc()) {
                        $medId = $row['med_id'];
                        echo "<tr>";
                        echo "<td data-lable='Medicine'>" . $row['med_name'] . "</td>";
                        echo "<td data-lable='Category'>" . $row['category'] . "</td>";
                        echo "<td data-lable='Type'>" . $row['type'] . "</td>";
                        echo "<td data-lable='Dosage'>" . $row['dosage'] . "</td>";
                        echo "<td data-lable='Stock'>" . $row['stock'] . "</td>";
                        echo "<td data-lable='Action'><button class='restockbtn' onclick=\"restockModal('" . $row['med_id'] . "', '" . $row['med_name'] . "', '" . $row['dosage'] . "')\" data-modal-target='#restockForm' ><i class='fas fa-cubes'></i>
                <span class='button-text'>Restock</span></button></td>";
                        echo "</tr>";
                            }
                        echo    "</tbody>";     
                        echo    "</table>";
                        } else {
                        echo "No medicine available.";
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
      <div class="modal" id="addForm" >
        <div class="modal-body" >
        <div class="medformcontainer">
            <header>Medicine Registration</header>
            <form id="myForm" method="POST">
                <div class="forms">

                    <div class="fieldsMarginTop">
                        <div class="input-fields">
                            <label for="">Medicine Name</label>
                            <input id="1stform" type="text" name="medName" placeholder="Enter Medicine Name" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Category</label>
                            <select class="fill" name="category" required><br>
                        <option class="fill" value="generic">Generic</option>
                        <option class="fill" value="branded">Branded</option>
                    </select>
                            
                        </div>

                        <div class="input-fields">
                            <label for="">Type</label>
                            <select class="fill" name="type" required><br>
                        <option class="fill" value="capsule">Capsule</option>
                        <option class="fill" value="tablet">Tablet</option>
                        <option class="fill" value="sachet">Sachet</option>
                        <option class="fill" value="ampule">Ampule</option>
                        <option class="fill" value="tube">Tube</option>
                    </select>
                        </div>
                        <div class="input-fields">
                            <label for="">Dosage</label>
                            <input id="1stform" type="text" name="dosage" placeholder="Enter Dosage" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Description</label>
                            <input id="1stform" type="text" name="description" placeholder="Enter Description" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Usage</label>
                            <input id="1stform" type="text" name="usage" placeholder="Enter Usage" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Quantity</label>
                            <input id="1stform" type="number" name="quantity" placeholder="Enter Quantity" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Acquisition</label>
                            <input  type="date" max="<?php echo $today; ?>" name="acquisition" placeholder="Enter Date of Acquisition" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Expiration</label>
                            <input  type="date" min="<?php echo $today; ?>" name="expiration" placeholder="Enter Date of Expiration" required>
                        </div>


                    </div>
                
                    <div class="btn-box">
                        <button type="submit" class="submit" name="submit_medicine" id="submit">Submit</button>
                    </div>
                </div>
                    
            </form>
            
        </div>
        </div>
        </div>
        <!-- Update Form -->
        <div class="modal"  id="updateForm" >
        <div class="modal-content" >
        <div class="medformcontainer">
            <header>Medicine Registration</header>
            
            <form id="myForm" method="POST">
                <div class="forms">
                
                    <span class="title">Medicine Information</span>
                    <input type="hidden" id="med_id" name="med_id">

                    <div class="fields">
                        <div class="input-fields">
                            <label for="">Medicine Name</label>
                            <input id="med_name" type="text" name="med_name" placeholder="Enter your Medicine Name" required>
                        </div>
                        <div class="input-fields">
                            <label for="">Category</label>
                            <select class="fill" name="category" id="category" required><br>
                        <option class="fill" value="generic">Generic</option>
                        <option class="fill" value="branded">Branded</option>
                    </select>
                            
                        </div>

                        <div class="input-fields">
                            <label for="">Type</label>
                            <select class="fill" id="type" name="type" required><br>
                        <option class="fill" value="capsule">Capsule</option>
                        <option class="fill" value="liquid">Liquid</option>
                        <option class="fill" value="tablet">Tablet</option>
                    </select>
                            
                        </div>

                        <div class="input-fields">
                            <label for="">Stock</label>
                            <input id="stock" type="number" name="stock" placeholder="Enter your Middle Name" required>
                        </div>

                    </div>
                
                    <div class="btn-box">
                        <button type="submit" class="submit" name="update_medicine" id="submit">Submit</button>
                    </div>
                </div>
                    
            </form>
            
        </div>
        </div>
        </div>
        <!-- Restock Form -->
    <div class="pmodal" id="restockForm" >
        <div class="modal-body" >
        <div class="presformcontainer">
            <header>Medicine Restock: <span style="color: #14359e;" id="viewmed_name"></span></header>
            
            <form id="myForm" method="POST">
                <div class="forms">
                    <input type="hidden" id="viewmed_id" name="med_id">
                    <div class="fieldsMarginTop">
                    <div class="input-fields">
                            <label for="">Quantity</label>
                            <input  type="number" name="quantity" placeholder="Enter Quantity" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Acquisition</label>
                            <input  type="date" max="<?php echo $today; ?>" name="acquisition" placeholder="Enter Date of Acquisition" required>
                        </div>

                        <div class="input-fields">
                            <label for="">Expiration</label>
                            <input  type="date" min="<?php echo $today; ?>" name="expiration" placeholder="Enter Date of Expiration" required>
                        </div>

                    </div>
                
                    <div class="btn-boxMc">
                        <button type="submit" class="submit" name="restock_medicine" id="submit">Submit</button>
                    </div>
                </div>
                    
            </form>
            
        </div>
        </div>
        </div>
        <div id="overlay"></div>
    <div id="toastBox"></div>
    <script>
            const form = document.getElementById("myForm");
            const inputs = form.getElementsByTagName('input');
            const forms = document.getElementById("form1");

            var form1 = document.getElementById("form1");
            var form2 = document.getElementById("form2");
            var form3 = document.getElementById("form3");

            var next = document.getElementById("next");
            var back1 = document.getElementById("back1");
            var next1 = document.getElementById("next1");
            var back2 = document.getElementById("back2");

            var progress = document.getElementById("progress");
            

            next.addEventListener('click', (e) => {
              
                let errorMessage = '';
                for (let i = 0; i < 12; i++) {
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
              
              let errorMessage = '';
              for (let i = 0; i < 20; i++) {
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
                    let success = '<span>&#10004;</span> Sucessfully Submitted';
                    let error = '<span>&#10008;</span> PLease fix the error';
                    let invalid = "<span>&#33;</span> Invalid input, Please complete the form";
            function showToast(msg){
                let toast = document.createElement('div');
                toast.classList.add('toast');
                toast.innerHTML = msg;
                toastBox.appendChild(toast);

                if(msg.includes('error')){
                    toast.classList.add('error');
                }
                if(msg.includes('invalid')){
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
// Function to open the modal
function updateModal(med_id, med_name, category, type, stock) {
    document.getElementById('med_id').value = med_id;
    document.getElementById('med_name').value = med_name;
    document.getElementById('category').value = category;
    document.getElementById('type').value = type;
    document.getElementById('stock').value = stock;
}
function restockModal(med_id, med_name, dosage) {
    document.getElementById('viewmed_id').value = med_id;
    document.getElementById('viewmed_name').textContent = med_name + ' ' + dosage;

}
</script> 

 <!-- Initialize select2 for the searchable dropdown -->
<script>
    $(document).ready(function() {
        $('#medicine').select2({
            placeholder: "Select Medicine",
            allowClear: true
        });
    });
</script> 
<?php
// Registration Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_medicine"])) {
    // Retrieve form data
    $medName = strtolower(htmlspecialchars($_POST["medName"]));
    $category = htmlspecialchars($_POST["category"]);
    $type = htmlspecialchars($_POST["type"]);
    $dosage = htmlspecialchars($_POST["dosage"]);
    $description = htmlspecialchars($_POST["description"]);
    $usage = htmlspecialchars($_POST["usage"]);
    $quantity = htmlspecialchars($_POST["quantity"]);
    $acquisition = htmlspecialchars($_POST["acquisition"]);
    $expiration = htmlspecialchars($_POST["expiration"]);
    
    
    // Step 1: Check if the medicine exists in the medicineinfo_tbl
$sql = "SELECT med_id FROM medicineinfo_tbl WHERE med_name = ? && category = ? && type = ? && dosage = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $medName,$category,$type,$dosage);
$stmt->execute();
$result = $stmt->get_result();

// If the medicine exists
if ($result->num_rows > 0) {
    // Fetch the med_id
    $row = $result->fetch_assoc();
    $med_id = $row['med_id'];

    // Step 2: Get the latest stock from inventory_tbl with the latest date_created
    $sql = "SELECT stock, date_created FROM inventory_tbl WHERE med_id = ? ORDER BY date_created DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If there is existing stock
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existing_stock = $row['stock'];
    } else {
        // If no stock exists, set existing_stock to 0
        $existing_stock = 0;
    }

    // Step 3: Add the new stock value to the existing stock
    $total_stock = $existing_stock + $quantity;

    // Step 4: Insert the new total stock into inventory_tbl with the current date
    $sql = "INSERT INTO inventory_tbl (med_id, stock, date_created) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $med_id, $total_stock);

    if ($stmt->execute()) {
        $stmt2 = $conn->prepare("INSERT INTO order_tbl (med_id, acquisition, expiration, quantity) 
              VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("issi", $med_id, $acquisition, $expiration, $quantity);
        $stmt2->execute();
        $user_id = $_SESSION['user_id'];
            $action = "Restocked Medicine: " . $medName . " Stock: " . $quantity;
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $action);
            $stmt->execute();
            echo "<script>
                          Swal.fire('Success', 'New Medicine registered successfully!', 'success').then(() => {
                           window.location.replace('Inventory');
                      });
                  </script>";
    } else {
        echo "<script>Swal.fire('Error', 'Error inserting medicine: ' . $stmt->error, 'error');</script>";
    }

} else {
    // Medicine does not exist, insert it into medicineinfo_tbl
    $sql1 = "INSERT INTO medicineinfo_tbl (med_name, category, type, dosage, description, `usage`) VALUES (?,?,?,?,?,?)";
    $stmt1 = $conn->prepare($sql1);
    if ($stmt1 === false) {
        echo "Error preparing statement: " . $conn->error;
    } else {
        $stmt1->bind_param("ssssss", $medName,$category, $type, $dosage, $description, $usage);
        // ...
    }
    if ($stmt1->execute()) {
        // Get the new med_id for the inserted medicine
        $med_id = $stmt1->insert_id;

        // Insert the new stock into inventory_tbl with the current date
        $sql = "INSERT INTO inventory_tbl (med_id, stock, date_created) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $med_id, $quantity);
        $stmt2 = $conn->prepare("INSERT INTO order_tbl (med_id, acquisition, expiration, quantity) 
              VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("issi", $med_id, $acquisition, $expiration, $quantity);
        $stmt2->execute();

        if ($stmt->execute() && $stmt2->execute()) {
            $user_id = $_SESSION['user_id'];
            $action = "Registered Medicine: " . $medName . " Stock: " . $quantity;
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $action);
            $stmt->execute();
            echo "<script>
                          Swal.fire('Success', 'New Medicine registered successfully!', 'success').then(() => {
                           window.location.replace('Inventory');
                      });
                  </script>";
        
        } else {
            echo "<script>Swal.fire('Error', 'Error inserting stock: ' . $stmt->error, 'error');</script>";
        }
    } else {
        echo "<script>Swal.fire('Error', 'Error inserting medicine: ' . $stmt->error, 'error');</script>";
        
    }
}      
    } 
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_medicine"])){
    $med_id = htmlspecialchars($_POST['med_id']);
$med_name = htmlspecialchars($_POST['med_name']);
$category = htmlspecialchars($_POST['category']);
$type = htmlspecialchars($_POST['type']);
$stock = htmlspecialchars($_POST['stock']);
    $sql = "UPDATE inventory_tbl SET med_name = ?, category = ?, type = ?, stock = ? WHERE med_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $med_name, $category, $type,$stock, $med_id);
    
    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    
    $stmt->close();
    
    // Redirect back to the main page or display a success message
    header("Location: Inventory"); // Replace with your actual main page
}
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["restock_medicine"])){
    $med_id = htmlspecialchars($_POST['med_id']);
    $quantity = htmlspecialchars($_POST["quantity"]);
    $acquisition = htmlspecialchars($_POST["acquisition"]);
    $expiration = htmlspecialchars($_POST["expiration"]);

    $sql_medicine = "SELECT med_name FROM medicineinfo_tbl WHERE med_id = ?";
    $stmt_medicine = $conn->prepare($sql_medicine);
    $stmt_medicine->bind_param("s", $med_id);
    $stmt_medicine->execute();
    $result_medicine = $stmt_medicine->get_result();
    $med_name = $result_medicine->fetch_assoc()['med_name'];

    $sql_check = "SELECT * FROM inventory_tbl WHERE med_id = ? ORDER BY date_created DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $med_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
              
if ($result->num_rows > 0) {
    // Product exists, so update the stock
    $row = $result->fetch_assoc();
    $new_stock = $row['stock'] + $quantity;
    $sql = "INSERT INTO inventory_tbl (med_id, stock, date_created) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $med_id, $new_stock);
        
    if ($stmt->execute()) {
    $stmt2 = $conn->prepare("INSERT INTO order_tbl (med_id, acquisition, expiration, quantity) 
              VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("issi", $med_id, $acquisition, $expiration, $quantity);
    $stmt2->execute();
    $user_id = $_SESSION['user_id'];
            $action = "Restocked Medicine: " . $med_name . " Quantity: " . $quantity;
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $action);
            $stmt->execute();
            echo "<script>
                    Swal.fire('Success', 'Medicine restocked successfully!', 'success').then(() => {
                        window.location.replace('Inventory');
                    });
                  </script>";
        
    } else {
        echo "<script>Swal.fire('Error', 'Error restocking medicine: ' . $stmt->error, 'error');</script>";
    }
    
    $stmt->close();
    
    // Redirect back to the main page or display a success message
    // Replace with your actual main page
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
</body>
</html>