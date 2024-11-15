<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Clinic Dashboard</title>
  
  <link rel="stylesheet" href="loginstyle.css">
  <!-- Font Awesome Cdn Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="scriptpop.js"></script>
  <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
  
</head>

<body>
        <div class="login-section"> 
        <div class="welcome-section">
          <img src="images/clinicLogo.png" alt="neust logo">
        </div>
            <h1>Infirmary Portal</h1>
            <p>Welcome!</p>
            <form id="loginForm" action="login.php" method="post">
            <p class="label">Email</p>
                <div class="input-group">
                    <input type="text" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                <p class="label">Password</p>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="********" required>
                    <span class="show-password">
                        <i id="togglePassword" class="far fa-eye"></i>
                    </span>
                </div>
                <div class="option">
                <div class="button-container">
                        <a class="acctUpdateBtn" data-modal-target='#updatePassForm'>Forgot Password?</a>
                    </div>
                </div>
               <a href="">
                <button type="submit">Log in</button>
                </a>
            </form>
    
    </div>
<!-- Change password Form -->
<div class="pmodal" id="updatePassForm">
        <div class="modal-body" >
        <div class="presformcontainer">
            <header>Forgot Password?</header>
            <form id="myForm" method="POST">
<div class="forms">
        
        <span class="title">Change Password</span>
        <input type="hidden" id="user_id"name="user_id" required>
        <div class="fields">
            <form action="" id="otpForm">
            <input type="hidden" id="output" name="otpGen">
            <div class="input-group">
            <label for="Password"><b>Email</b></label>
            <input class="form-control" type="email" name="email" placeholder="example@Email" required>
            <button type="submit" id="otpbtn" name="otprequest" onclick="generate_password()">Request OTP</button> 
            </div>
            <!-- Span element that will be hidden initially -->
            <span id="visibleSpan" style="display: none;">Success: OTP is sent to your email!</span>
            <script src="generatepassword.js"></script>   
            </form>
            <div class="input-group">
            <label for="Confirm"><b>OTP</b></label>
            <input class="form-control" type="text" name="otp" placeholder="OTP Code" > 
    </div>  

<div class="btn-box">
    <button type="submit" name="updatePass" onclick="generate_password()">Update</button>
</div>
</div>
</div>
    
            </form>
            
        </div>
    </div>
    </div> 
    <div id="overlay"></div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission
            const formData = new FormData(this);
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Continue',
                confirmButtonColor: '#14359e',
                background: '#fff',
                color: '#1f1f29',
                iconColor: 'green',
                customClass: {
                 confirmButton: 'custom-confirm-button',  
                 }
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'Retry',
                confirmButtonColor: '#ff4d00',
                background: '#fff',
                color: '#1f1f29',
                iconColor: '#f44336',
                customClass: {
                 confirmButton: 'custom-confirm-button',  
                 }
                 });
              }
           })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred.',
            icon: 'error',
            confirmButtonText: 'Retry',
            confirmButtonColor: '#ff4d00',
            background: '#fff',
            color: '#1f1f29',
            iconColor: '#f44336',
            customClass: {
                 confirmButton: 'custom-confirm-button',  
                 }
             });
         });
    });
    </script>
    
    <!-- Add JavaScript for password toggle -->
    <script>
      const togglePassword = document.querySelector('#togglePassword');
      const password = document.querySelector('#password');

      togglePassword.addEventListener('click', function (e) {
          // Toggle the type attribute
          const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
          password.setAttribute('type', type);
          // Toggle the icon
          this.classList.toggle('fa-eye-slash');
      });
    </script>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include ('conn.php');
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otprequest'])) {
    $email = $_POST['email'];
    $otp = $_POST['otpGen'];

    $checkemail = mysqli_query($conn, "SELECT * FROM account_tbl WHERE email = '$email' LIMIT 1");
    $count2 = mysqli_num_rows($checkemail);

    if($count2 != 1) {
        echo "Error: Email does not exist!";
    } else {
        $sql = "UPDATE account_tbl SET otp = ? WHERE email = ?";
        $stmtinsert = $conn->prepare($sql);
        $stmtinsert->bind_param("ss", $otp, $email);
        $result = $stmtinsert->execute();
        $conn->commit();

        if ($result) {
            $greeting = "Password Reset Verification";
            $link = "http://localhost/clin/index.php";
            $tag1 = "We received a request to reset the password for your account associated with this email address. If you made this request, this is your OTP is: ";
            $message = $greeting . "<br>" .$link  . "<br>" .  $tag1 . $otp . "<br>" ;

            // PHPMailer object
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'clinicportal2024@gmail.com';
                $mail->Password = 'iqeq uauy xsqg rada';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('clinicportal2024@gmail.com', 'Clinic Portal');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = "Password Reset Request for Your Account";
                $mail->Body = $message;
                $mail->send();
                echo "<script>
                 Swal.fire('Success', 'OTP sent successfully!', 'success')
          </script>";
            } catch (Exception $e) {
                echo "<script>
                 Swal.fire('Error', 'OTP Not Sent!', 'error')
          </script>";
                // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatePass'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    $pass = $_POST['otpGen'];
    $password = password_hash($_POST['otpGen'], PASSWORD_DEFAULT);
    
    $checkotp = mysqli_query($conn, "SELECT * FROM account_tbl WHERE otp = '$otp' && email = '$email' LIMIT 1");
    $count2 = mysqli_num_rows($checkotp);
    if($count2 != 1) {
        echo "Error: OTP does not match!";
    } else {
                // Transaction for updates
                $conn->begin_transaction();
                try {
  
                    // Update `account_tbl`
                    $sql2 = "UPDATE account_tbl SET otp = '', password = ? WHERE email = ?";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param('ss', $password, $email);
                    $result = $stmt2->execute();
  
                    // Commit transaction
                    $conn->commit();
                    if ($result) {
                        $greeting = "New Password";
                        $link = "http://localhost/clin/index.php";
                        $tag1 = "You have requested to reset your password. Your new password is: ";
                        $message = $greeting . "<br>" .$link  . "<br>" .  $tag1 . $pass . "<br>" ;
            
                        // PHPMailer object
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'clinicportal2024@gmail.com';
                            $mail->Password = 'iqeq uauy xsqg rada';
                            $mail->SMTPSecure = 'tls';
                            $mail->Port = 587;
                            $mail->setFrom('clinicportal2024@gmail.com', 'Clinic Portal');
                            $mail->addAddress($email);
            
                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = "Welcome to Clinic Management System";
                            $mail->Body = $message;
                            $mail->send();
                            echo "<script>
                          Swal.fire('Success', 'New password is sent to your email successfully!', 'success')
                          </script>";
                        } catch (Exception $e) {
                            echo "<script>
                             Swal.fire('Error', 'New password is not sent to your email successfully!', 'error')
                      </script>";
                            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        }
                    }
                    
                } catch (Exception $e) {
                    // Rollback transaction in case of error
                    $conn->rollback();
                    echo "<script>
                          Swal.fire('Error', 'Error updating password: {$e->getMessage()}', 'error');
                          </script>";
                }
            
        } 
    }

?>
</body>
</html>