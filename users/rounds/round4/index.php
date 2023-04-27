<?php
require ("connect.php");

session_start(); // start session to get username 


if (isset($_SESSION["user_id"])){
	$user_id = $_SESSION["user_id"];	
}
else {
	header("Location: ../");
}

// get the username to set the JDenticon
$query = "SELECT `username` FROM `user` WHERE `user_id`=?";

if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
    $stmt->bind_param("s", $user_id);
}
$result = select_sql($stmt);

if ($result->num_rows > 0){
	// fetch the data from DB
	while ($row = $result->fetch_assoc()){
		$username = $row["username"]; // get the list of friends (CSV) into an array
	}
}
/*
	Get the ID's of friends and store them in a CSV string
*/
$query = "SELECT * FROM `friends` WHERE `user_id`=?";

if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
    $stmt->bind_param("i", $user_id);
}
$result = select_sql($stmt);

if ($result->num_rows > 0){
	// fetch the data from DB
	while ($row = $result->fetch_assoc()){
		$friends_id_str = $row["list_of_friends"]; // get the list of friends (CSV) into an array
		$blocked_id_str = $row["blocked_list"]; // get the list of blocked friends (CSV) into an array
	}
}

if ($friends_id_str != ""){ // check if user has friends
	/*
		Populate the page if friends found.
		Use the ID list to get their names and add it to the friends list on page
	*/

	$friends_ids = explode(",", $friends_id_str);
	$list_of_friends = array();

	// loop through list of friends ID's and fetch their name
	$query = "SELECT `user_id`,`username` FROM `user` WHERE `user_id` IN (?)";
	
	foreach ($friends_ids as $friend_id){
		if ($stmt = $conn->prepare($query)) {
		/* bind parameters for markers */
			$stmt->bind_param("s", $friend_id);
		}
		$result = select_sql($stmt);
		
		if ($result->num_rows > 0){
			// fetch the data from DB
			while ($row = $result->fetch_assoc()){
				array_push($list_of_friends, $row);
			}
		}
	}
}

if ($blocked_id_str != ""){
	$blocked_ids = explode(",", $blocked_id_str);
}

if (count($blocked_ids) >= 1){
	$blocked = array_intersect($blocked_ids, $friends_ids);
}

// fetch the inventory of the user
$query = "SELECT * FROM `user_score` WHERE `user_id`=?";

$m_content = $_GET["m_content"];

if (!file_exists("start")){
	echo "<script>window.location.replace(\"../queue.php\");</script>";
}

$round_num = file_get_contents("admin/round.txt");

echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Client</title>
	<link rel="stylesheet" href="styles/css/main.css">
	<link rel="stylesheet" href="styles/css/home_page.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<link rel="stylesheet" href="styles/modal-style.css">
	
	<style>
		#virus_icon{
			visibility: hidden;
		}
		.alert {
		  padding: 20px;
		  background-color: #f44336;
		  color: white;
		  position: fixed;
		  top: 0;
		  left: 0;
		  z-index: 999;
		  width: 98%;
		  height: 23px;
		}

		.closebtn {
		  margin-right: 15px;
		  color: white;
		  font-weight: bold;
		  float: right;
		  font-size: 22px;
		  line-height: 20px;
		  cursor: pointer;
		  transition: 0.3s;
		}

		.closebtn:hover {
		  color: black;
		}
	</style>
</head>
<body>
';

echo '
  <div id="page-wrapper">
    	
	<div class="top-bar">		  
	  <div class="navigation-container space-evenly">
		  <div id="timer-container"></div>
		  <p class="nav">Round: ' . $round_num . '</p>
		  <p class="nav">Number of friends: ' . sizeof($list_of_friends) . '</p>
		  <p class="nav">Sent Messages: ' . get_total_messages_sent() . '</p>
	  </div>
	</div>
  </div>
  
  <div class="top-background"></div>
  
  <!-- Divide page into 3 parts -->
	  
	  	<div class="container">
		<div id="left">
		<div class="left-description" id="left-description"><p class="center-in-div" style="color: white; margin-left: 43%;">&nbsp;&nbsp;</p></div>
		<button class="collapsible">Profile & Inventory</button>
		<div class="content">
			<div class="center-in-column">
				<div id="top-left" class="inner-left">
				<h1 style="margin: 0px">Profile</h1>
				<svg data-jdenticon-value="' . $username . '" width="70" height="70" style="background: white; margin-left: 7px; margin-top: 10px; border-radius: 50%">Fallback text or image for browsers not supporting inline svg.</svg>	
				<div id="profile-text">
					<span class="profile-info">';
					
				
						echo '
						<table>
						<tr>
							<td valign="middle">@' . $username . '</td>
							<td valign="top"><img src="img/virus.png" id="virus_icon" style="width: 25px; margin-top"/></td>
						</tr>
						</table>
						';
					echo '
					</span>
					<span class="profile-info" id="DATE"></span>
					<span class="profile-info">
					<table>
						<tr>
						</tr>
					</table>
					</span>	
				</div>
				</div>
				
				<div id="running-score" style="width: 51%; margin-left: 45%">
						<!-- Score here --!>
				</div>
				
				<div class="inner-left" style="width: 48%">
					<br>
					<p><strong>Score:</strong></p>
					<p style="text-align: left">The score is calcuated by taking the number of messages you send and deducting a the number of compromised messages you open.</p>
					<br>
				</div>
				<div class="inner-left" style="width: 48%">
					<br>
					<p><strong>Scenario:</strong></p>
					<p style="text-align: left">In this scenario you are allowed to interact with a preset list of friends. Here you can only send a message at random to one of your friends, and you will also be chosen at random on their side. The message you recieve might be potentially compromised.</p><br>
				</div>
				</div>
			</div>
		</div>
		
		<div id="right">
		<div class="right-description" id="right-description"><p class="center-in-div" style="color: white; margin-left: 43%;">&nbsp;&nbsp;</p></div>
		<button class="collapsible" style="margin-bottom: 40px;">Friends list</button>
		<div class="content">
			<div class="center-in-column">
					<form action="" method="post">
					<div id="middle_right" class="send-item-right">
						<h3 style="float: left; width: 150px; height: 100px; margin-top: 10px">Send to Random Friend</h3>
							<input id="send_item_image" class="rand_btn" type="image" src="img/send_item_small.png"/>
							</a>						
						</form>
					</div>
				</div>
			</div>
			</div>
		<div id="middle">
		<div class="center-description" id="center-description"><p class="center-in-div" style="color: white; margin-left: 46%;">Interactions</p></div>
		<button class="collapsible" id="interactions_btn">Interactions</button>
		<div class="content">
		<div class="nav-bar">
			<p class="toggle" id="toggle">
				<span id="left-span"> Timeline </span>
			</p>
			<div id="left-nav"> 	
					<div class="center-in-column">
						<div id="timeline-container" style="overflow-y: scroll;"></div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
 <div id="cmVjb3Zlcg"></div>
 
  <script src="script/client_app.js"></script>
  <script>  
	// call when page loads
	reloadTimer(); 
	
	var timeout = setInterval(reloadTimer, 1000); // update after 1s
	function reloadTimer(){
		$(\'#timer-container\').load(\'script/timer.php\');
	}
  
  </script>
  <script>  
	// call when page loads
	reloadTimeline(); 
	//recover();
	//reloadRank();

	var timeout_timeline = setInterval(reloadTimeline, 1500); // update after 2.5s
	function reloadTimeline(){
		$(\'#timeline-container\').load(\'script/load_timeline_blank.php\');
	}
    
	//var timeout = setInterval(recover, 3000); // update after 3s
	//function recover(){
	//	$(\'#cmVjb3Zlcg\').load(\'script/recover_user.php\');
	//}
	
  </script>
  <script>
	var coll = document.getElementsByClassName("collapsible");
	var i;

	for (i = 0; i < coll.length; i++) {
	  coll[i].addEventListener("click", function() {
		this.classList.toggle("active");
		var content = this.nextElementSibling;
		if (content.style.maxHeight){
		  content.style.maxHeight = null;
		} else {
		  content.style.maxHeight = content.scrollHeight + "px";
		} 
	  });
	}
</script>
  <script src="https://cdn.jsdelivr.net/npm/jdenticon@2.1.1" async></script>
<script>

var today = new Date();
var dd = today.getDate();
var mm = today.getMonth() + 1; //January is 0!

var yyyy = today.getFullYear();
if (dd < 10) {
  dd = \'0\' + dd;
} 
if (mm < 10) {
  mm = \'0\' + mm;
} 
var today = dd + \'/\' + mm + \'/\' + yyyy;
document.getElementById(\'DATE\').innerText = "Joined: " + today;

var timeout = setInterval(reloadRank, 1000); // update after 2.5s
function reloadRank(){
	$(\'#running-score\').load(\'script/running_rank.php\');
}

// Get the modal
var modal = document.getElementById("myModal");
// Get the button that opens the modal
var btn = document.getElementById("send_item_image");
btn.addEventListener("click", function(event){
  event.preventDefault()
  modal.style.display = "block";
});
// Get the <span> element that closes the modal
//var span = document.getElementsByClassName("close")[0];
// When the user clicks on <span> (x), close the modal
//span.onclick = function() {
  //modal.style.display = "none";
//}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
$(document).ready(function(){
    $(\'.rand_btn\').click(function(){
        var clickBtnValue = $(this).val();
        var ajaxurl = \'random_file.php\',
		data = {\'friend_list\' : "' . $friends_id_str . '"};
        $.post(ajaxurl, data, function (response) {
            // Response div goes here.
           document.body.innerHTML = document.body.innerHTML + response;
        });
    });
});
</script>
</body>
</html>';

function get_total_messages_sent(){
	global $conn;
	global $round_num, $user_id; 
	
	$total = 0;
	
	$query = "SELECT * FROM `round_score` WHERE `user_id`=? AND `round_num`=?";
	
	if ($stmt = $conn->prepare($query)){
		$stmt->bind_param("ii", $user_id, $round_num);
	}
	$result = select_sql($stmt);
	
	if ($result->num_rows >0){
		while ($row = $result->fetch_assoc()){
			 $sent_clean = $row["clean_messges_sent_in_round"];
			 $sent_infected = $row["infected_messges_sent_in_round"];
			 $total = $sent_clean + $sent_infected;
		}
	}
	
	return $total;
}

// regex for matching file = ^3_\d+(.*).json$

function user_is_blocked($friends_ids_list){
	global $conn;
	$blocked_by = array();
	$friends_ids = explode(",", $friends_ids_list);
	
	foreach ($friends_ids as $id){
		$query = "SELECT * FROM `friends` WHERE `user_id`=?";
		if ($stmt = $conn->prepare($query)){
			$stmt->bind_param("i", $id);
		}
		$result = select_sql($stmt);
	
		if ($result->num_rows >0){
			while ($row = $result->fetch_assoc()){
				 $l = $row["blocked_list"];
				 if (!is_null($l) and $l != ""){
				 $list = explode(",", $l);
				 if (count($list) > 0){
					 if (!in_array($id, $list)){						 
						array_push($blocked_by, $id);
					 }
				 }
				 }

			}
		}
	}
	return $blocked_by;
}

$conn->close();
?>