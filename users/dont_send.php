<?php

require ("connect.php");
require ("track_activity.php");

session_start(); // start session to get username 

$file = $_GET["file"];
$infected = $_GET["infected"];

$friends_list = explode(',', $_GET["friends"]);

$randIndex = array_rand($friends_list);

$send_to = $friends_list[$randIndex];

$is_infected = $infected;

track_activity();

header("Location: index.php");
die();

function track_activity(){
	global $send_to, $is_infected, $file;
	$sender_username = get_username($_SESSION["user_id"]);
	$receiver_username = get_username($send_to);
	
	$sender_filename = "data/tracking/$sender_username.xml";
	$receiver_filename = "data/tracking/$receiver_username.xml";
	
	$message_id = $_SESSION["user_id"] . "_" . $send_to . "_" . time();
	
	$f_type = get_file_type($file);

	update_tracking($sender_filename, "didnt_sent", $message_id, $send_to, NULL, time(), $is_infected, $is_infected, $f_type); // sender tag
	
}

function user_is_infected($user_id){
	global $conn;
	$query = "SELECT * FROM `user` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			return $row["user_infected"];
		}
	}
	
	return false;
}

function get_username($user_id){
	global $conn;
	
	$query = "SELECT * FROM `user` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()){
			return $row["username"];
		}
	}
}

function get_file_type($f){
	if (filter_var($f, FILTER_VALIDATE_URL)) { 
	  return "link";
	}
	else {
		$a = explode("/", $f);
		return $a[2];
	}
}

?>