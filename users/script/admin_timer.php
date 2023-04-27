<?php
require ("../connect.php");

if (file_exists("../start")){
	$timer = 3*60; // seconds
	$timestamp_file = 'end_timestamp.txt';
	if(!file_exists($timestamp_file))
	{
	  file_put_contents($timestamp_file, time()+$timer);
	}
	$end_timestamp = file_get_contents($timestamp_file);
	$current_timestamp = time();
	$difference = $end_timestamp - $current_timestamp;

	if ($difference <= -3){
		unlink('end_timestamp.txt');
		unlink('end_timestamp_score.txt');
		unlink('../start');   
	}
	if($difference <= 0)
	{
	 $score_timer = 1*90;
	 $score_timestamp = 'end_timestamp_score.txt';
	 if(!file_exists($score_timestamp))
	 {
	   file_put_contents($score_timestamp, time()+$score_timer);
	 }
	  array_map( 'unlink', array_filter((array) glob("../../items/*") ) );
	  update_winner();
	  //unlink("../start");
	  //unlink("end_timestamp.txt");
	  //unlink("end_timestamp_score.txt");
	  // execute your function here
	  // reset timer by writing new timestamp into file
	}
	else
	{
	  echo '<p class="nav">Time left in round: ' . gmdate("i:s", $difference).'s </p>';
	}
}
	
function update_winner(){
	global $conn;

	$query = "SELECT * FROM `round_score` ORDER BY ABS(total)";
	$stmt = $conn->prepare($query);

	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$user_id = $row["user_id"];
		
		if (score_exists($user_id)){
			update_score($user_id);
		}
		else {
			add_score($user_id);
		}
	}
}


function score_exists($user_id){
	global $conn;
	
	$query = "SELECT * FROM `final_rank` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("i", $user_id);
	}
		
	if ($result->num_rows > 0){
		return true;
	}
}

function update_score($user_id){	
	global $conn;
	
	$query = "SELECT * FROM `final_rank` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		$row = $result->fetch_assoc();
		
		$won_time = $row["won_times"];
		$won_time += 1;
		
		$query = "UPDATE `final_rank` SET `won_times`=? WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)){
			$stmt->bind_param("ii", $won_time, $user_id);
		}
		update_sql($stmt);
	}
}
	
function add_score($user_id){	
	global $conn;
	
	$query = "INSERT INTO `final_rank` (won_times, user_id) VALUES (?, ?)";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("ii", $total=1, $user_id);
	}
	insert_sql($stmt);
}

?>