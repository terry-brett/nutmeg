<?php
require ("connect.php");
session_start();

if (isset($_SESSION["user_id"])){
	$user_id = $_SESSION["user_id"];	
}
else {
	header("Location: ../");
}

$user_id = $_SESSION["user_id"];

$total_points = abs(get_clean_message_sent($user_id) +get_infected_message_sent($user_id));
$times_infected = get_times_infected($user_id);

$final_score = round(calc_final_score($total_points, $times_infected));

update_user_score($user_id, $final_score);

$score = get_clean_message_sent($user_id) + get_infected_message_sent($user_id) - get_infected_message_recieved($user_id);

$round_num = file_get_contents("admin/round.txt");

echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
  <style>
@import url("https://fonts.googleapis.com/css?family=Gothic A1");
body{
	background-color: #263238;
	font-style: inherit;
	font-family: \'Gothic A1\';
	color: white;
}
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 30%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
tr:nth-child(even) {
  background-color: #37474F;
}

@media screen and (max-width: 1366px){
	table{
		width: 50%;
	}
}
@media screen and (max-width: 1100px){
	table{
		width: 60%;
	}
}
@media screen and (max-width:945px) {  
	table{
		width: 90%;
	}
}
</style>
</head>
<body>

<h2 style="width: 30%; margin-left:auto;  margin-right:auto;">Round ' . $round_num . ' stats</h2>

<table style="margin-left:auto;  margin-right:auto;">
  <tr>
    <th>Category</th>
    <th>Value</th>
  </tr>
  <tr>
    <td>Score</td>
    <td>' . get_current_round_score($user_id, $round_num) . '</td>
  </tr>'; 
  if ($round_num >= 2){
	  echo '
	  <tr>
		<td style="color: red;">Infected by in this round</td>
		<td style="color: red;">' . get_current_infected_by($user_id, $round_num) . '</td>
	  </tr>';
  }
  else if ($round_num == 1){
	  $infected = get_current_infected_by($user_id, $round_num);
	  if ($infected == '' or $infected == Null){
		  echo '
		  <tr>
			<td style="color: red;">Infected this round</td>
			<td style="color: red;">No</td>
		  </tr>';
	  }
	  else {
		  echo '
		  <tr>
			<td style="color: red;">Infected this round</td>
			<td style="color: red;">Yes</td>
		  </tr>';
	  }
  }
echo '
<tr>
    <td>Rank this round</td>
    <td>' . get_current_rank($user_id, $round_num) . '</td>
</tr>
<tr>
	<td>Clean messages sent</td>
	<td>' . get_current_clean_message_sent($user_id, $round_num) . '</td>
</tr>
<tr>
	<td>Clean messages received</td>
	<td>' . get_current_clean_message_recieved($user_id, $round_num) . '</td>
</tr>
<tr>
	<td>Infected messages sent</td>
	<td>' . get_current_infected_message_sent($user_id, $round_num) . '</td>
</tr>
<tr>
	<td>Infected messages received</td>
	<td>' . get_current_infected_message_recieved($user_id, $round_num) . '</td>
</tr>
</table>
<br>

<h2 style="width: 30%; margin-left:auto;  margin-right:auto;">Overall stats</h2>

<table style="margin-left:auto;  margin-right:auto;">
  <tr>
    <th>Category</th>
    <th>Value</th>
  </tr>
  <tr>
    <td>Score</td>
    <td>' .  /*rtrim(preg_replace("/,+/", ",", implode(",", $inventory)), ',')*/   get_total_across_all_rounds($user_id) . '</td>
  </tr>
  <tr>
    <td>Infection penalty</td>
    <td>' . get_infected_by_across_all_rounds($user_id) . '</td>
  </tr>';
  
  if ($round_num >= 2){
	  echo '
	  <tr>
		<td style="color: red;">Infected by</td>
		<td style="color: red;">' . get_infected_by($user_id) . '</td>
	  </tr>';
  }
  
echo '
<tr>
    <td>Rank in round 1</td>
    <td>' .  get_rank_from_round($user_id, 1). '</td>
</tr>
<tr>
    <td>Rank in round 2</td>
    <td>' .  get_rank_from_round($user_id, 2). '</td>
</tr>
<tr>
    <td>Rank in round 3</td>
    <td>' .  get_rank_from_round($user_id, 3). '</td>
</tr>
<tr>
    <td>Rank in round 4</td>
    <td>' .  get_rank_from_round($user_id, 4). '</td>
</tr>
</table>
<br>

<h2 style="width: 30%; margin-left:auto;  margin-right:auto;">Infection stats</h2>
<table style="margin-left:auto;  margin-right:auto;">
  <tr>
    <td>Times infected</td>
    <td>' . get_times_infected($user_id) . '</td>
  </tr>';
if ($round_num >= 3){
  echo '
  <tr>
    <td>Times recovered</td>
    <td>' . get_times_recovered($user_id) . '</td>
  </tr>';
}
echo '<tr>
		<td style="color: red;">Fractions of Intected Nodes</td>
		<td style="color: red;">' . get_fraction_of_infected_nodes($round_num) . '</td>
	  </tr>
	  <tr>
		<td style="color: red;">Infected Friends</td>
		<td style="color: red;">' . get_infected_friends($user_id, $round_num) . ' of your friends got infected</td>
	  </tr>
';

echo '
</table>
<br>

<h2 style="width: 30%; margin-left:auto;  margin-right:auto;">Activity  stats</h2>
<table style="margin-left:auto;  margin-right:auto;">
  <tr>
    <td>Clean messages sent</td>
    <td>' . get_clean_message_sent($user_id) . '</td>
  </tr>
  <tr>
    <td>Clean messages received</td>
    <td>' . get_clean_message_recieved($user_id) . '</td>
  </tr>
  <tr>
    <td>Infected messages sent</td>
    <td>' . get_infected_message_sent($user_id) . '</td>
  </tr>
  <tr>
    <td>Infected messages received</td>
    <td>' . get_infected_message_recieved($user_id) . '</td>
  </tr>

</table>

<h2 style="width: 30%; margin-left:auto;  margin-right:auto;"></h2>
<table style="margin-left:auto;  margin-right:auto;">
  <tr>
    <th>Total Score</th>
    <th>' . $final_score . '</th>
  </tr>
</table>

<center><div id="timer-container"></div></center>

<script>	
	reloadTimer(); 

	var timeout = setInterval(reloadTimer, 1000);
	function reloadTimer(){
		$(\'#timer-container\').load(\'script/score_timer.php\');
	}
</script>

</body>
</html>

  ';

function get_total_score($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["items_total_value"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
			
		}
	}
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

function get_current_clean_message_sent($user_id, $round_num){
	global $conn;
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

function get_current_clean_message_recieved($user_id, $round_num){
	global $conn;
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

function get_current_infected_message_sent($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
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

function get_current_infected_message_recieved($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
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

function get_times_infected($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);

	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["times_infected"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
}

function get_infected_by($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_by"];			
			if (is_null($v)){
				return 0;
			}
			else if ($v == ""){
				return "None";
			}
			else {
				$s = "";
				$new_array = array_count_values(str_getcsv(rtrim($v,',')));
				while (list ($key, $val) = each ($new_array)) {
					//echo "$key (x$val),";
					$s .= "$key (x$val),";
				}
				return rtrim($s,',');			
			}
		}
	}
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

function get_current_round_score($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()){
			$v = $row["total"];
			if (is_null($v)){
				return 0;
			}
			else {
				return $v;
			}
		}
	}
	return 0;
}

function get_current_infected_by($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_by_in_round"];			
			if (is_null($v)){
				return 0;
			}
			else if ($v == ""){
				return Null;
			}
			else {
				$s = "";
				$new_array = array_count_values(str_getcsv(rtrim($v,',')));
				while (list ($key, $val) = each ($new_array)) {
					//echo "$key (x$val),";
					$s .= "$key (x$val),";
				}
				return rtrim($s,',');			
			}
		}
	}
}

function get_total_across_all_rounds($user_id){
	global $conn;
	$query = "SELECT SUM(`total`) FROM `round_score` WHERE `user_id` = ?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			return $row['SUM(`total`)'];
		}
	}
}

function get_infected_by_across_all_rounds($user_id){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);
	$c = 0;
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$v = $row["infected_by_in_round"];			
			if (is_null($v)){
				return 0;
			}
			else if ($v == ""){
				return "None";
			}
			else {
				$s = "";
				//$new_array = array_map('trim',array_filter(explode(',',$v)));
				$c = $c + count(array_map('trim',array_filter(explode(',',$v))));
			}
		}
	}
	return $c;
}

function get_fraction_of_infected_nodes($round_num){
	global $conn;
	$query = "SELECT COUNT(*) FROM `round_score` WHERE NOT `infected_by_in_round`='' AND `round_num`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $round_num);
	}
	
	$result = select_sql($stmt);
	
	$infected_count = 0;
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$infected_count = $row['COUNT(*)'];
		}
	}
	
	$query = "SELECT COUNT(*) FROM `user` WHERE NOT `username`='admin'";
	$stmt = $conn->prepare($query);
	$result = select_sql($stmt);
	
	$total_user_count = 1;
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			$total_user_count = $row['COUNT(*)'];
		}
	}
	
	return round((($infected_count/$total_user_count) * 100), 2) . "%"; // round to 2 d.p
}

function get_infected_friends($user_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `friends` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()){
			$friends_id_str = $row["list_of_friends"]; // get the list of friends (CSV) into an array
		}
	}
	
	if ($friends_id_str != ""){
		$friends_ids = explode(",", $friends_id_str);
	}

	$query = "SELECT `user_id` FROM `round_score` WHERE NOT `infected_by_in_round`='' and `round_num`=?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $round_num);
	}
		
	$result = select_sql($stmt);
	
	$user_id_list = array();
	
	if ($result->num_rows > 0){
		while ($row = $result->fetch_assoc()) {
			array_push($user_id_list, $row['user_id']);
		}
	}
	
	return count(array_intersect($user_id_list, $friends_ids));
}

function get_current_rank($my_id, $round_num) {
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `round_num` = " . $round_num . " ORDER BY ABS(total) DESC";
	
	$stmt = $conn->prepare($query);
	$result = select_sql($stmt);
	
	$count = 1;
	
	if ($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$user_id = $row["user_id"];
		$user_total = $row["total"];		
			if ($user_id == $my_id){
				return addOrdinalNumberSuffix($count);
			}
			else {
				$count += 1;
			}
		}
	}
	return "None";
}

function get_rank_from_round($my_id, $round_num){
	global $conn;
	$query = "SELECT * FROM `round_score` WHERE `round_num` = " . $round_num . " ORDER BY ABS(total) DESC";
	
	$stmt = $conn->prepare($query);
	$result = select_sql($stmt);
	
	$count = 1;
	
	if ($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$user_id = $row["user_id"];
		$user_total = $row["total"];		
			if ($user_id == $my_id){
				return addOrdinalNumberSuffix($count);
			}
			else {
				$count += 1;
			}
		}
	}
	return "None";
}

function calc_final_score($total_points, $times_infected){
	$bonus = 0;
	$virus_free_bonus = 0;
	$virus_penalty = 0;
	$target_met_bonus = 0;
	
	if ($times_infected == 0){
		$virus_free_bonus = (float)get_constant("virus_free_bonus");
	}
	else if ($times_infected > 0) {
		$virus_penalty = (float)get_constant("virus_penalty");
	}
		
	$total_penalty = 0;
	for ($i = 0; $i < $times_infected; $i++){
		$total_points = $total_points * (1-$virus_penalty);
	}

	return $total_points + ($bonus * $total_points) + ($target_met_bonus * $total_points);
}

function get_constant($const_name){
	$xml=simplexml_load_file("constants.xml") or die("Error: Cannot create object");
	return $xml->$const_name;
}

function update_user_score($user_id, $score){
	global $conn;
	$query = "UPDATE `user_score` SET `final_round_score`=? WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("ii", $score, $user_id);
	}
	update_sql($stmt);
}

function addOrdinalNumberSuffix($num) {
    if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        // Handle 1st, 2nd, 3rd
        case 1:  return $num.'st';
        case 2:  return $num.'nd';
        case 3:  return $num.'rd';
      }
    }
    return $num.'th';
}

$conn->close();
?>