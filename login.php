<?php
require ("users/connect.php");

$query = "SELECT * FROM `user` WHERE `username`=?";

if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
    $stmt->bind_param("s", $_POST["username"]);
}
$result = select_sql($stmt);

//$stmt = mysqli_prepare($conn, $query);
//mysqli_stmt_bind_param($stmt, "s", $_POST["username"]);

if ($result->num_rows > 0) {
	session_start();
	$_SESSION["username"] = $_POST["username"];
	while($row = $result->fetch_assoc()) {
		$hash = $row["password"];
        $_SESSION["user_id"] = $row["user_id"];
    }	
	if (password_verify($_POST["password"], $hash)){
		if ($_POST["username"] == "admin"){
			header("Location: users/admin/admin.php");
			die();
		}
		else {
			$_SESSION["item_count"] = 10;
			header("Location: questionnaire.php");
			die();
		}
	}
	else {
		header("Location: /prototype_basic/?login_error=true");
		die();
	}
} else {
	header("Location: /prototype_basic/?login_error=true");
	die();
}

?>