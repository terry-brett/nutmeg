<?php
require ("connect.php");
require ("track_activity.php");

session_start(); // start session to get username 

$from_id = $_SESSION["user_id"];

$friends_list = explode(',', $_GET["friends"]);

$randIndex = array_rand($friends_list);

$send_to = $friends_list[$randIndex];

$round_num = file_get_contents("admin/round.txt");

$file = $_GET["file"];

echo "<br>";

$items = array(1);

// get random index from array $items
$randIndex = array_rand($items);

$to_array = array(array("id" => $send_to, "opened" => false));

//$is_infected = rand(0,1) < 0.5;
$is_infected = user_is_infected($from_id);

$myObj = new stdClass();
$myObj->origin = $from_id;
$myObj->to = $to_array; // array that will keep track of item history and propagation
$myObj->infected = $is_infected;
$myObj->item_value = $items[$randIndex];
$myObj->loc = $file;

$time_now = time();

$filename = "../items/$send_to" . "_$from_id". "_$time_now.json";

if (file_exists($filename)){
	$filename = "../items/" . file_newname("../items/","$send_to" . "_$from_id". "_$time_now.json");
	//echo $send_to;	
}

$fp = fopen($filename, 'w');
fwrite($fp, json_encode($myObj));
fclose($fp);

// Add item to timeline, keep the score between the user interactions

/*
	Keep track of interactions between the users here, and update the timeline
	Then calculate the total score based on that
	Compute the number of times two users interacted with each other based on their ids -- count that into the table
	ID's kept in the recieved from
*/

// user_id = send to this user
// recieved_from = recieved from this user

if (row_exists($from_id, $send_to) or row_exists($send_to, $from_id)){
	// increase sent_to_user
	$sent_to_user = get_sent_to_items($from_id, $send_to) + 1;
	$recieved_from_user = get_recieved_from_times($from_id, $send_to) + 1;
	
	$query = "UPDATE `timeline` SET `sent_to_user`=? WHERE `user_id`=? AND `recieved_from`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("iii", $sent_to_user, $from_id, $send_to);
	}

	$executed = update_sql($stmt);
		
	$query = "UPDATE `timeline` SET `recieved_from_user`=? WHERE `user_id`=? AND `recieved_from`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("iii", $recieved_from_user, $send_to, $from_id);
	}

	$executed = update_sql($stmt);
}
else {
	$query = "INSERT INTO `timeline` (user_id, recieved_from, sent_to_user, recieved_from_user, infected) VALUES (?, ?, 0, 1, 0)";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $send_to, $from_id);
	}
	$executed = insert_sql($stmt);
	
	$query = "INSERT INTO `timeline` (user_id, recieved_from, sent_to_user, recieved_from_user, infected) VALUES (?, ?, 1, 0, 0)";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $from_id, $send_to);
	}
	$executed = insert_sql($stmt);
}

// increase score for infected/clean messages

if ($is_infected == true){
	$query = "UPDATE `user` SET `user_susceptible`=1 WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $send_to);
	}

	$executed = update_sql($stmt);
	
	if(score_exists($send_to)){
		$infected_recieved = get_infected_message_recieved($send_to); // target		
		$infected_recieved ++;
		
		$query = "UPDATE `user_score` SET `infected_message_recieved`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $infected_recieved, $send_to);
		}

		$executed = update_sql($stmt);
	}
	else {
		// if row doesn't exists create one
		$query = "INSERT INTO `user_score` (user_id, infected_message_recieved) VALUES(?, 1)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("i", $send_to);
		}
		$executed = insert_sql($stmt);
	}
	if (score_exists($from_id)){
		$infected_sent = get_infected_message_sent($from_id); // origin
		$infected_sent ++;
		
		$query = "UPDATE `user_score` SET `infected_message_sent`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $infected_sent, $from_id);
		}
		$executed = update_sql($stmt);
	}
	else {
		// if row doesn't exists create one
		$query = "INSERT INTO `user_score` (user_id, infected_message_sent) VALUES(?, 1)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("i", $from_id);
		}
		$executed = insert_sql($stmt);
	}
	
	if (current_score_exists($send_to)){
		$infected_recieved = get_current_infected_message_recieved($send_to); // target		
		$infected_recieved ++;
		
		$query = "UPDATE `round_score` SET `infected_messges_received_in_round`=? WHERE `user_id`=? AND `round_num`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("iii", $infected_recieved, $send_to, $round_num);
		}

		$executed = update_sql($stmt);
	}
	else {
		$query = "INSERT INTO `round_score` (user_id, infected_messges_received_in_round, round_num) VALUES(?, 1, ?)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $send_to, $round_num);
		}
		$executed = insert_sql($stmt);
	}
	
	if (current_score_exists($from_id)){
		$infected_sent = get_current_infected_message_sent($from_id); // origin		
		$infected_sent ++;
		
		$query = "UPDATE `round_score` SET `infected_messges_sent_in_round`=? WHERE `user_id`=? AND `round_num`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("iii", $infected_sent, $from_id, $round_num);
		}
		$executed = update_sql($stmt);
	}
	else {
		$query = "INSERT INTO `round_score` (user_id, infected_messges_sent_in_round, round_num) VALUES(?, 1, ?)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $from_id, $round_num);
		}
		$executed = insert_sql($stmt);
	}
}
else if ($is_infected == false){
	if(score_exists($send_to)){
		$clean_recieved = get_clean_message_recieved($send_to); // target		
		$clean_recieved ++;
		
		$query = "UPDATE `user_score` SET `clean_message_recieved`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $clean_recieved, $send_to);
		}
		$executed = update_sql($stmt);
	}
	else {
		// if row doesn't exists create one
		$query = "INSERT INTO `user_score` (user_id, clean_message_recieved) VALUES(?, 1)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("i", $send_to);
		}

		$executed = insert_sql($stmt);
	}
	if (score_exists($from_id)){
		$clean_sent = get_clean_message_sent($from_id); // origin
		$clean_sent ++;
		
		$query = "UPDATE `user_score` SET `clean_message_sent`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $clean_sent, $from_id);
		}
		$query = update_sql($stmt);
	}
	else {
		// if row doesn't exists create one
		$query = "INSERT INTO `user_score` (user_id, clean_message_sent) VALUES(?, 1)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("i", $from_id);
		}

		$executed = insert_sql($stmt);
	}
	
	// update running rank
	if (current_score_exists($send_to)){
		$clean_msg = get_current_clean_message_recieved($send_to);
		$clean_msg ++;
		
		$query = "UPDATE `round_score` SET `clean_messges_received_in_round`=? WHERE `user_id`=? AND `round_num`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("iii", $clean_msg, $send_to, $round_num);
		}
		$executed = update_sql($stmt);
	}
	else {
		$query = "INSERT INTO `round_score` (user_id, clean_messges_received_in_round, round_num) VALUES(?, 1, ?)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $send_to, $round_num);
		}

		$executed = insert_sql($stmt);
	}
	
	if (current_score_exists($from_id)){
		$clean_msg = get_current_clean_message_sent($from_id);
		$clean_msg ++;
		
		$query = "UPDATE `round_score` SET `clean_messges_sent_in_round`=? WHERE `user_id`=? AND `round_num`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("iii", $clean_msg, $from_id, $round_num);
		}
		$query = update_sql($stmt);
	}
	else {
		$query = "INSERT INTO `round_score` (user_id, clean_messges_sent_in_round, round_num) VALUES(?, 1, ?)";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $from_id, $round_num);
		}

		$executed = insert_sql($stmt);
	}
}

track_activity();
update_running_score($from_id, 1);

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

function row_exists($from_id, $to_id){
	global $conn;
	$query = "SELECT * FROM `timeline` WHERE `user_id`=? AND `recieved_from`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $to_id, $from_id);
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

function get_sent_to_items($from_id, $to_id){
	global $conn;
	$query = "SELECT * FROM `timeline` WHERE `user_id`=? AND `recieved_from`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $from_id, $to_id);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()){
			return $row["sent_to_user"];
		}
	}
}

function get_recieved_from_times($from_id, $to_id){
	global $conn;
	$query = "SELECT * FROM `timeline` WHERE `user_id`=? AND `recieved_from`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $to_id, $from_id);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()){
			return $row["recieved_from_user"];
		}
	}
}

function score_exists($to_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $to_id);
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

function current_score_exists($to_id){
	global $conn;	
	global $round_num;
	
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";

	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $to_id, $round_num);
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

function get_clean_message_sent($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["clean_message_sent"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
			
		}
	}
}

function get_current_clean_message_sent($user_id){
	global $conn;	
	global $round_num;
	
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["clean_messges_sent_in_round"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
			
		}
	}
}

function get_clean_message_recieved($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["clean_message_recieved"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
}

function get_current_clean_message_recieved($user_id){
	global $conn;	
	global $round_num;
	
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["clean_messges_received_in_round"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
}

function get_current_infected_message_sent($user_id){
	global $conn;	
	global $round_num;
	
	$query = "SELECT * FROM `round_score` WHERE `user_id`=?  AND `round_num`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_messges_sent_in_round"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
}

function get_infected_message_sent($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_message_sent"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
}

function get_current_infected_message_recieved($user_id){
	global $conn;	
	global $round_num;
	
	$query = "SELECT * FROM `round_score` WHERE `user_id`=?  AND `round_num`=?";
	echo $query;
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_messges_received_in_round"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
}

function get_infected_message_recieved($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_message_recieved"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
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

function update_running_score($user_id, $score){
		global $conn;
		global $is_infected;
		
		$round_num = file_get_contents("admin/round.txt");
		$total = 0;
		
		$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
		
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $user_id, $round_num);
		}
		
		$result = select_sql($stmt);		
		
		if ($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$total = $row["total"];
			}
		}
		
		if ($is_infected == true){
			$total = $total - 1;
		}
		
		$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
		if ($stmt = $conn->prepare($query)) {
			$stmt->bind_param("ii", $user_id, $round_num);
		}
		$result = select_sql($stmt);
		if ($result->num_rows > 0){
			$query = "UPDATE `round_score` SET `total`=? WHERE `user_id`=? AND `round_num`=?";
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
		
		$query = "UPDATE `user_score` SET `items_total_value`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)){
			$stmt->bind_param("ii", $total, $user_id);
		}
		update_sql($stmt);
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

$conn->close();

function get_file_type($f){
	if (filter_var($f, FILTER_VALIDATE_URL)) { 
	  return "link";
	}
	else {
		$a = explode("/", $f);
		return $a[2];
	}
}


function track_activity(){
	global $send_to, $is_infected, $file;
	$sender_username = get_username($_SESSION["user_id"]);
	$receiver_username = get_username($send_to);
	
	$sender_filename = "data/tracking/$sender_username.xml";
	$receiver_filename = "data/tracking/$receiver_username.xml";
	
	$message_id = $_SESSION["user_id"] . "_" . $send_to . "_" . time();
	
	$f_type = get_file_type($file);

	update_tracking($sender_filename, "sent", $message_id, $send_to, NULL, time(), $is_infected, $is_infected, $f_type); // sender tag
	update_tracking($receiver_filename, "received", $message_id, NULL, $_SESSION["user_id"], time(), $is_infected, $is_infected, $f_type); // reciever tag
	
}

header("Location: index.php");
die();
?>