<?php
require ("../connect.php");
require ("../track_activity.php");

session_start(); // start session to get username 

$user_id = $_SESSION["user_id"];

$filename = $_GET['filename'];
$from_id = $_GET['from_id'];
$btn_type = $_GET['btn_type'];

$filename = decrypt($filename);
$from_id = decrypt($from_id);

// Read JSON file
$json = file_get_contents("../../items/" . $filename);

//Decode JSON
$json_data = json_decode($json,true);


$from_user_id = explode("(", explode(".", explode("_", $filename)[1])[0])[0];

//Print data
$opened = $json_data['to'][sizeof($json_data['to']) - 1]['opened'];
$file_infected = $json_data['infected'];

if ($file_infected == true){
	if ($btn_type == "not_likely"){
		$is_infected = is_user_infected($user_id);
		
		// if the user is not infected and the file is infected, the score for infection will increment
		//if ($is_infected == 0){
			// increment score table
			increase_infection_score($user_id);
		//}
		
		$query = "UPDATE `user` SET `user_infected`=True,`user_susceptible`=False WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("i", $user_id);
		}
		update_sql($stmt);
	}
}

$inventory = serialize(array(base64_encode($filename)));
$row_result = row_exists($user_id);
$item = base64_encode($filename);

if ($opened == false){
	$json_data['to'][sizeof($json_data['to']) - 1]['opened'] = true;
	//Encode the array back into a JSON string.
	$json = json_encode($json_data);
	//Save the file.
	file_put_contents('../../items/' . $filename, $json);
	
	// quick fix to duplicate names appearing in base64 
	$new_name = base64_encode($filename) . '.json';
	if (file_exists("../../items/" . $new_name)){
		$new_name = file_newname("../../items/",$new_name);	
		$item = explode(".",$new_name)[0];
		rename('../../items/' . $filename, '../../items/' . $new_name);
	}
	else {
		// rename the file so the gift disappears from the main page
		rename('../../items/' . $filename, '../../items/' . $new_name);
	}
}

if ($file_infected == true){
	if ($btn_type == "not_likely"){
		$infected_by = get_username($from_user_id) . ",";
	}
}
else {
	$infected_by = "";
}

if ($row_result == 0){
	$query = "INSERT INTO `user_score` (user_id, infected_by, item_inventory) VALUES (?, ?, ?)";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("iss", $user_id, $infected_by, $inventory);
	}
	$executed = insert_sql($stmt);
}
else if(row_is_null($user_id)){
	$query = "UPDATE `user_score` SET `item_inventory`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("si", $inventory, $user_id);
	}
	$executed = update_sql($stmt);
}
else {
	while ($row = $row_result->fetch_assoc()) {
		$inventory = unserialize($row['item_inventory']);
	}
	if (!in_array($item, $inventory)) { // check if item in inventory
		array_push($inventory, $item);
		$inventory = serialize($inventory);
		$query = "UPDATE `user_score` SET `item_inventory`=?, infected_by=CONCAT(infected_by, ?) WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ssi", $inventory, $infected_by, $user_id);
		}
		update_sql($stmt);
	}	
}

update_running_score($user_id, $infected_by);
track_activity();
header("Location: ../index.php?m_content=" . $file_infected . "&m_id=" . $from_user_id);
die();

function decrypt($string, $key = 'MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAIMpvz+6pqAK/tQrUwC3l2Tma1zsojSOzvee0fmP9uqa4fcUUwDZjOhUDQg/ig0TX38mnSBK10ItHeq422+grVECAwEAAQ==', 
$secret = 'MIIBOwIBAAJBAIMpvz+6pqAK/tQrUwC3l2Tma1zsojSOzvee0fmP9uqa4fcUUwDZjOhUDQg/ig0TX38mnSBK10ItHeq422+grVECAwEAAQJAJr0BuzTJWaNluAxDq4aNtENJmlxZW+SBxCioI2kdqBQ9lnRP5fMm6Bwm5woqpMd2uOjaEVTeCwAYBHMGznV4AQIhAPWWue+I79j9VPkktHrX7WDrbietFPh+3oOjHF8L8sHhAiEAiLk0sK12u3CkoDMYjdHSGJnB1hNqkgBvanKia8IOOXECIQDm9Y39L/noRi5gc91rXZ/3MtGQbJy5KY8XmxD2beUp4QIgWK85nDiIQYEJZ9h83tDw5IAnmUKy581cd8Gv1RHkxCECIQCEvJDEFl1WYDk7KwwF8HWrB2mk+YZF7BEPpOJbpHtpJA==', $method = 'AES-256-CBC') 
{
    // hash
    $key = hash('sha256', $key);
    // create iv - encrypt method AES-256-CBC expects 16 bytes
    $iv = substr(hash('sha256', $secret), 0, 16);
    // decode
    $string = base64_decode($string);
    // decrypt
    return openssl_decrypt($string, $method, $key, 0, $iv);
}

function row_exists($user_id){
		global $conn;
		// check if user score table exists
		$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("i", $user_id);
		}
		$result = select_sql($stmt);
		if ($result->num_rows == 0){
			return 0;
		}
		else {
			return $result;
		}
}

function row_is_null($user_id){
	global $conn;
	// check if user score table exists
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$v = $row["item_inventory"];
			if (is_null($v)){
				return true;
			}
			else {
				return false;
			}
		}
	}
	return false;
}

function file_newname($path, $filename){
    if ($pos = strrpos($filename, '.')) {
           $name = substr($filename, 0, $pos);
           $ext = substr($filename, $pos);
    } else {
           $name = $filename;
    }

    $newpath = $path.'/'.$filename;
    $newname = $filename;
    $counter = 0;
    while (file_exists($newpath)) {
           $newname = $name .'('. $counter . ')' . $ext;
           $newpath = $path.'/'.$newname;
           $counter++;
     }

    return $newname;
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

function is_user_infected($user_id){
	global $conn;
	$query = "SELECT `user_infected` FROM `user` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$infected = $row["user_infected"];
			if ($infected == 1){
				return true;
			}
			else if ($infected == 0){
				return false;
			}
		}
	}
}

function increase_infection_score($user_id){
	global $conn;
	$times_infected = 0;
	
	// get current value
	$query = "SELECT `times_infected` FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		if (is_null($row["times_infected"])){
			$times_infected = 0;
		}
		while($row = $result->fetch_assoc()){
			$times_infected = $row["times_infected"];
		}
		$times_infected = $times_infected + 1;	
	}	
	
	$query = "UPDATE `user_score` SET `times_infected`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $times_infected, $user_id);
	}
	$executed = update_sql($stmt);
}

function track_activity(){
	global $from_user_id;
	global $file_infected;
	$username = get_username($_SESSION["user_id"]);
	
	$filename = "../data/tracking/$username.xml";
	
	$t = time();
	
	$message_id = $_SESSION["user_id"] . "_" . $from_user_id . "_" . $t;

	update_opened_item_tracking($filename, $message_id, $from_user_id, $t, $file_infected); // open item tag
	
}


function update_running_score($user_id, $infected_by){
		global $conn;
		global $file_infected, $btn_type;
		
		$total = 0;
		
		$round_num = file_get_contents("../admin/round.txt");
		
		$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
		
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $user_id, $round_num);
		}
		
		$result = select_sql($stmt);
		if ($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$cur_total = $row["total"];
			}
			
			if ($file_infected == true){
				echo $btn_type . "<br>" . $file_infected;
				if ($btn_type == "likely"){
					$cur_total += 1;
				}
				elseif ($btn_type == "not_likely"){
					$cur_total -= 10; # only case when the user gets infected
				}				
			}
			elseif ($file_infected == false){
				if ($btn_type == "likely"){
					$cur_total -= 1;
				}
				elseif ($btn_type == "not_likely"){
					$cur_total += 1;
				}
			}			
			
			$query = "UPDATE `round_score` SET infected_by_in_round=CONCAT(infected_by_in_round, ?), total=? WHERE `user_id`=? AND `round_num`=?";
			echo $query;
			if ($stmt = $conn->prepare($query)){
				$stmt->bind_param("siii", $infected_by, $cur_total, $user_id, $round_num);
			}
			update_sql($stmt);
		}
		else {
			$query = "INSERT INTO `round_score` (`user_id`, `total`, `round_num`, `infected_by_in_round`) VALUES (?, ?, ?, ?)";
			if ($stmt = $conn->prepare($query)){
				$stmt->bind_param("iiis", $user_id, $total, $round_num, $infected_by);
			}
			insert_sql($stmt);
		}
		
		$total = 0;
		
		$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
		
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $from_user_id, $round_num);
		}
		
		$result = select_sql($stmt);
		if ($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$total = $row["total"];
			}
			
			$total += 1;					
			
			$query = "UPDATE `round_score` SET total=? WHERE `user_id`=? AND `round_num`=?";
			echo $query;
			if ($stmt = $conn->prepare($query)){
				$stmt->bind_param("iii", $total, $from_user_id, $round_num);
			}
			update_sql($stmt);
		}
		else {
			$query = "INSERT INTO `round_score` (`user_id`, `total`, `round_num`) VALUES (?, ?, ?)";
			if ($stmt = $conn->prepare($query)){
				$stmt->bind_param("iiis", $from_user_id, $total, $round_num);
			}
			insert_sql($stmt);
		}
}

$conn->close();
?>