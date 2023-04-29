<?php

require ("users/connect.php");
session_start();

$username = $_POST["username"];
$password = $_POST["password"];
$secret_key_txt = $_POST["secret_key"];

$validate_key_value_num = "my_secret_key";

if ($secret_key_txt != $validate_key_value_num) {
        echo '<script>alert("Invalid key!"); window.location = \'index.php\';</script>';
}
else if ($secret_key_txt == $validate_key_value_num) {
        $pass_hash = password_hash($password, PASSWORD_DEFAULT); // keep constant as it changes in PHP versions

        $query = "INSERT INTO user (username, password, user_infected) VALUES (?, ?, ?)";

        $inf = 0;
        if ($stmt = $conn->prepare($query)) {
                /* bind parameters for markers */
                $stmt->bind_param("ssi", $username, $pass_hash, $inf);
        }

        $executed = insert_sql($stmt);

        if ($executed == true){
                // get user id
                $_SESSION["user_id"] = get_userid($username);
                $_SESSION["username"] = $username;
                header("Location: questionnaire.php");
                die();
        }
        else {
                echo '<script>alert("Error occured!"); window.location = \'index.php\';</script>';
                //header("Location: index.php");
                //die();
        }
}

function get_userid($username){
	global $conn;
	$query = "SELECT * FROM `user` WHERE `username`=?";
	if ($stmt = $conn->prepare($query)) {
		/* bind parameters for markers */
		$stmt->bind_param("s", $_POST["username"]);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["user_id"];
		}
	}
}
$conn->close();

?>
