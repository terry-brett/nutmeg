<?php
require ("../connect.php");
require ("../track_activity.php");

session_start();

$round_num = file_get_contents("../admin/round.txt");

$user_id = $_SESSION["user_id"];

$messages = glob('../../items/' . $user_id . '_*.json');

//if (user_is_infected($user_id)){
$infected_items = array();

foreach ($messages as $item){
	if (is_infected("../../items/" . $item . ".json")){
		array_push($infected_items, $item);
	}
}

foreach ($infected_items as $infected){
	$messages = array_diff( $messages, [$infected]);
	delete_item("../../items/" . $infected . ".json");
}

track_recovery();

if (score_exists($user_id, $round_num)){
	apply_penalty($user_id, $round_num);
}
	
//}

function apply_penalty($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	
	$result = select_sql($stmt);
	
	$total = 0;
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$total = $row['total'];			
		}
	}
	
	$total = $total - 10;
	
	$query = "UPDATE `round_score` SET `total`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $total, $user_id);
	}
	$executed = update_sql($stmt);
	
	$inf = 0;
	
	$query = "UPDATE `user` SET `user_infected`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $inf, $user_id);
	}
	$executed = update_sql($stmt);
}

function user_is_infected($user_id){
	global $conn;
	$query = "SELECT * FROM `user` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows >0){
		while ($row = $result->fetch_assoc()){
			if ($row["user_infected"] == 1){
				return true;
			}
			else {
				return false;
			}
		}
	}
	return false;
}

function score_exists($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	
	$result = select_sql($stmt);
	
	$total = 0;
	
	if ($result->num_rows > 0){
		return True;
	}
	else {
		return False;
	}
}

function is_infected($item){
	if (file_exists($item)){
		$file_content = file_get_contents($item);
		$arr = json_decode($file_content, true);
		$infected = $arr["infected"];
		
		if ($infected){
			return True;
		}
		
		return False;
	}
	return False;
}

function delete_item($item){
	unlink($item) or die("Couldn't delete file");
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

function track_recovery(){
	$username = get_username($_SESSION["user_id"]);
	
	$filename = "../data/tracking/$username.xml";
	
	$method = "manual";
	
	update_recovery_tracking($filename, $method, time());
}

$conn->close();
echo "Success";
?>