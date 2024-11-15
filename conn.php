<?php

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "cms";
$conn = new mysqli($servername,$username,$password,$db_name);
if ($conn->connect_error) {
	die("connection failed".$conn->connection_error);

}
echo "";


?>
