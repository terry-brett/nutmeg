<?php
require ("users/connect.php");

session_start(); 

$user_id = $_SESSION["user_id"];

$csv_line = array();

foreach ($_GET as $key => $value) { 
	array_push($csv_line,'' . $key . ":" . $value);
}

$fname = 'questionnaires/' . $user_id . '.csv';
$csv_line = implode(',',$csv_line);
if(!file_exists($fname)){$csv_line = $csv_line;}
$fcon = fopen($fname,'a');
$fcontent = $csv_line;
fwrite($fcon,$csv_line);
fclose($fcon);

$query = "UPDATE `user` SET `questionnaire_completed`=? WHERE `user_id`=?";
if ($stmt = $conn->prepare($query)) {
	$stmt->bind_param("ii", $v = 1, $user_id);
}
$executed = update_sql($stmt);

$conn->close();

header("Location: users/");
?>