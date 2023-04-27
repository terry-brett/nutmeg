<?php
require ("users/connect.php");
if(isset($_POST["username"]))
{
	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
		die();
	}
	
	$username = filter_var($_POST["username"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
	
	$query = "SELECT * FROM `user` WHERE `username`=?";

	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("s", $username);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		die('<img src="users/img/error.png" id="username_error" style="margin-left: 6px; width: 20px;"/>');
	}
	else {
		die('<img src="users/img/success.png" id="username_success" style="margin-left: 6px; width: 20px;"/>');
	}
}
?>