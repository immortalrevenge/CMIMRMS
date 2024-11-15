<?php
    session_start();

    include 'conn.php';
    if(isset($_POST)){
    
        // Get form data
// $email = $_POST['email'];
$email = $_POST['email'];
$pass = $_POST['password'];

// Prepare and bind
$stmt = $conn->prepare("SELECT username, password, user_id FROM account_tbl WHERE email = ? && dateDeleted = '0000-00-00'");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result( $username, $hashed_password, $user_id);
    $stmt->fetch();

    // Verify password
    if (password_verify($pass, $hashed_password)) {
        $action = "Login";
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user_id, $action);
            $stmt->execute();
        // Password is correct, start a session
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user_id;
        

        // Redirect based on role
        $redirect_url = 'Clinic';
        echo json_encode(['success' => true, 'message' => 'Login successful!', 'redirect' => $redirect_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    }

} else {

    if ($email == "admin" || $email == "ADMIN"){
        if ($pass == "adminClinicPass"){
            $_SESSION['username'] = "Admin";
            $_SESSION['user_id'] = 0;
            $action = "Login";
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $_SESSION['username'], $action);
            $stmt->execute();
            $redirect_url = 'Administrator';
            echo json_encode(['success' => true, 'message' => 'Login successful!', 'redirect' => $redirect_url]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Wrong password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No user found with that email.']);

    }
}
    }
$stmt->close();
$conn->close();
?>
