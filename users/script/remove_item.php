<?php
#MzJfMzNfMTU2NDMyODc0Ni5qc29u

require ("../connect.php");

session_start();

$user_id = $_SESSION["user_id"];

$to_delete = $_GET["itemid"];

$json = file_get_contents("../../items/" . $to_delete . ".json");
$json_data = json_decode($json,true);
$file_infected = $json_data['infected'];

update_running_score($user_id);

$inventory= unserialize(get_inventory($user_id));

$inventory = array_diff( $inventory, [$to_delete]);
update_total("../../items/" . $to_delete . ".json", $user_id);
delete_item("../../items/" . $to_delete . ".json");

$inventory = serialize($inventory);
update_inventory($user_id, $inventory);

function get_inventory($user_id){
global $conn;
$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
if ($stmt = $conn->prepare($query)) {
	$stmt->bind_param("i", $user_id);
}
$result = select_sql($stmt);
if ($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$v = $row["item_inventory"];
		if (is_null($v)){
			return Null;
		}
		else {
			return $v;
		}
	}
}
}


function update_running_score($user_id){
		global $conn;
		global $file_infected;
		
		$total = 0;
		
		$round_num = file_get_contents("../admin/round.txt");
		
		$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
		
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $user_id, $round_num);
		}
		
		$result = select_sql($stmt);
		if ($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$total = $row["total"];
			}
			
			if ($file_infected == true){
				$total = $total + 10;
			}			
			
			$query = "UPDATE `round_score` SET total=? WHERE `user_id`=? AND `round_num`=?";
			
			if ($stmt = $conn->prepare($query)){
				$stmt->bind_param("iii", $total, $user_id, $round_num);
			}
			update_sql($stmt);
		}
		else {
			$query = "INSERT INTO `round_score` (`user_id`, `total`, `round_num`) VALUES (?, ?, ?)";
			if ($stmt = $conn->prepare($query)){
				$stmt->bind_param("iii", $user_id, $total, $round_num);
			}
			insert_sql($stmt);
		}
		
}

function update_total($item, $user_id){
	global $conn;
	
	$total = get_current_total($user_id);
	
	if (!is_null($total)){
		$total += 0;
	}
	
	$query = "UPDATE `user_score` SET `items_total_value`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)){
			$stmt->bind_param("ii", $total, $user_id);
		}
		update_sql($stmt);
	
	return;
}

function get_current_total($user_id){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			return $row["total"];
		}
	}
	return Null;
}

function delete_item($item){
	unlink($item) or die("Couldn't delete file");
}

function update_inventory($user_id, $inventory){
	global $conn;
	$query = "UPDATE `user_score` SET `item_inventory`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("si", $inventory, $user_id);
	}
	$executed = update_sql($stmt);
	return;	
}

$conn->close();
echo "Success";
?>