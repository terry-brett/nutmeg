<?php

require ("connect.php");
require ("track_activity.php");

session_start();

$user_id = $_SESSION["user_id"];

$to_block_id = $_GET["to_block_id"];
$block_and_remove = $_GET["block_and_remove"];

block_user($user_id, $to_block_id);

if ($block_and_remove != ""){
	block_user($user_id, $block_and_remove);
	remove_items($user_id, $to_block_id);
}

function block_user($user_id, $to_block_id){
	global $conn;
	if (row_exists($user_id)){
		$blocked_list = get_blocked_list($user_id);
		if ($blocked_list == ""){
			$blocked_list .= $to_block_id;
		}
		else {
			$blocked_list .= "," . $to_block_id;
		}
		
		$query = "UPDATE `friends` SET `blocked_list`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("si", $blocked_list, $user_id);
		}
		$query = update_sql($stmt);
		
		$username = get_username($_SESSION["user_id"]);
		
		$filename = "data/tracking/$username.xml";
		update_blocked_item_tracking($filename, "block", $to_block_id, get_total_interaction($user_id, $to_block_id), time());
	}
}

function remove_items($user_id, $to_block_id){
	$messages = glob('../items/' . $user_id . '_' . $to_block_id . '*.json');
	foreach ($messages as $item){
		unlink($item);
	}		
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