<html>
    <head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="loginstyle.css">
    </head>
</html>

<?php
session_start();
include 'conn.php';



// Check user credentials

if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
            $action = "Logout";
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user_id, $action);
            $stmt->execute();
            session_destroy();
            echo "<script>
    Swal.fire('Success', 'Logout successfully!', 'success').then(() => {
        window.location.replace('index');
    });
</script>";
} else {
            $username = $_SESSION['username'];
            $action = "Logout";
            $sql = "INSERT INTO log_tbl (user_id, action) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $action);
            $stmt->execute();
            session_destroy();
            echo "<script>
    Swal.fire('Success', 'Logout Admin successfully!', 'success').then(() => {
        window.location.replace('index');
    });
</script>";
}

?>