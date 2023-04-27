<?php

require ("connect.php");
require ("track_activity.php");

session_start();

$user_id = $_SESSION["user_id"];

$unblock_id = $_GET["to_block_id"];

if (row_exists($user_id)){
	$blocked_list = get_blocked_list($user_id);
		
	//echo $blocked_list;
	$unblocked_list = str_replace($unblock_id, "", $blocked_list);
	if ($unblocked_list[0] == ","){
		$unblocked_list = substr($unblocked_list, 1);
	}
	
	
	$query = "UPDATE `friends` SET `blocked_list`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("si", $unblocked_list, $user_id);
	}
	$query = update_sql($stmt);
	
	$username = get_username($_SESSION["user_id"]);
	
	$filename = "data/tracking/$username.xml";
	update_blocked_item_tracking($filename, "unblock", $unblock_id, get_total_interaction($user_id, $unblock_id), time());
}

function row_exists($user_id){
	global $conn;
	$query = "SELECT * FROM `friends` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		return true;
	}
	else {
		return false;
	}
	return false;
}

function get_blocked_list($user_id){
	global $conn;
	$query = "SELECT `blocked_list` FROM `friends` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	
	if (is_null($result)){
		return "";
	}
	else {
		if ($result->num_rows > 0){
			while ($row = $result->fetch_assoc()){
				return $row["blocked_list"];
			}
		}
	}
}

function get_total_interaction($user_id, $recieved_from){
	global $conn;
	$query = "SELECT * FROM `timeline` WHERE `user_id`=? AND `recieved_from`=?";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("ii", $user_id, $recieved_from);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()){
			$sent = $row["sent_to_user"];
			$recieved = $row["recieved_from_user"];
			$total = $sent + $recieved;
			return $total;
		}
	}
}

function get_username($user_id){
	global $conn;
	$query = "SELECT `username` FROM `user` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	$username = "";
	if ($result->num_rows > 0){
		// fetch the data from DB
		while ($row = $result->fetch_assoc()){
			$username = $row["username"]; 
		}
	}
	
	return $username;
}

$conn->close();

header("Location: index.php");
die();

?>