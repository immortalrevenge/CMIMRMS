<?php
    session_start();

    include 'conn.php';
    if(isset($_POST)){
    
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
        $cpassword = $_POST['cpassword'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // chechk if the username is available

        if ($pass == $cpassword){
        $checkusername = mysqli_query($conn, "SELECT * FROM account_tbl WHERE username = '$username' LIMIT 1");
        $count = mysqli_num_rows($checkusername);
        
        if($count == 1){
            // username already exist
            echo json_encode(['success' => false, 'message' => 'Username already exist']);
            
        } else {
            $checkprc = mysqli_query($conn, "SELECT * FROM user_tbl WHERE prc_id = '$prc_id' LIMIT 1");
            $count2 = mysqli_num_rows($checkprc);
            if($count2 == 1){
                echo json_encode(['success' => false, 'message' => 'PRC Number already exist']);
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
                    echo json_encode(['success' => true, 'message' => 'Successfully saved']);
                }else{ 
                    echo json_encode(['success' => false, 'message' => 'Unable to save']);
                } 
            }
        }  
    }else{ 
        echo json_encode(['success' => false, 'message' => 'Password does not match']);
    } 
    
} else {
    echo json_encode(['success' => false, 'message' => 'No data']);
}
    ?>