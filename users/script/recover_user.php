<?php
require("../connect.php");
require ("../script/probability_distribution_library/UniformDistribution.php");
require ("../track_activity.php");
session_start();

$user_id = $_SESSION["user_id"]; 

$mu = 0.1;

if (is_infected($user_id)){
	/*echo "
	<script>
		document.getElementById(\"virus_icon\").style.visibility = \"visible\";
	</script>
	";*/
	
	echo "
		<script>
			document.getElementById(\"top-left\").style.backgroundColor  = \"#f21111\";
			document.getElementById(\"profile-header\").innerText   = \"Profile Infected\";
		</script>
	";
}
else {
	/*echo "
	<script>
	var img = document.getElementById('virus_icon');
	img.style.visibility = 'hidden';
	</script>
";*/
	echo "
		<script>
			document.getElementById(\"top-left\").style.backgroundColor  = \"#37474F\";
			document.getElementById(\"profile-header\").innerText   = \"Profile\";
		</script>
	";
}

/*
if (score_exists($user_id)){
	$tr = get_times_recovered($user_id);
	$tr += 1;
	
	$ud = new UniformDistribution(0, 1);
	$random_number = $ud->_getRNG();
	//echo $random_number;
	if ($random_number < $mu){
		if (is_infected($user_id)){
			track_recovery();
			$query = "UPDATE `user` SET `user_infected`=0 WHERE `user_id`=?";
			if ($stmt = $conn->prepare($query)) {
				$stmt->bind_param("i", $user_id);
			}
			update_sql($stmt);
			
			$query = "UPDATE `user_score` SET `times_recovered`=? WHERE `user_id`=?";
			if ($stmt = $conn->prepare($query)) {
				$stmt->bind_param("ii", $tr, $user_id);
			}
			update_sql($stmt);
		}
	}
}*/

function score_exists($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
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

function get_times_recovered($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["times_recovered"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
			
		}
	}
}

function is_infected($user_id){
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
	
	$method = "auto";
	
	update_recovery_tracking($filename, $method, time());
}

?>