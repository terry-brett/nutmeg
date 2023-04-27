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
$m_id = $_GET["m_id"];

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
		#myProgress {
		  width: 100%;
		  background-color: #ddd;
		}

		#myBar {
		  width: 1%;
		  height: 30px;
		  background-color: #90A4AE;
		}
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

if (!is_null($m_content)){
	if ($m_content == true){
		echo '
		<div class="alert">
		  <span class="closebtn" onclick="this.parentElement.style.display=\'none\';">&times;</span> 
		  <strong>Alert!</strong> You have been infected by ' . get_username($m_id) . '
		</div>
		';
	}
}


echo '
  <div id="page-wrapper">
    	
	<div class="top-bar">		  
	  <div class="navigation-container space-evenly">
		  <div id="timer-container"></div>
		  <p class="nav">Round: ' . $round_num . '</p>
		  <p class="nav">Number of friends: ' . sizeof($list_of_friends) . '</p>
		  <img id="notification-blank" class="notification" src="img/bell_icon_blank.png"/>
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
				<div id="running-score">
					<!-- Score here --!>
				</div>
				</div>
				
				<div class="inner-left" id="antivirus">		
					<div class="container">
						<div class="column" style="width: 20%;">
							<input id="av_btn" type="image" src="img/av_icon.png" style="width: 60px; margin-left: -10px;"/>
						</div>
						<div class="column" style="width: 80%;">
							<p>Click to scan your profile</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="right">
		<div class="right-description" id="right-description"><p class="center-in-div" style="color: white; margin-left: 43%;">&nbsp;&nbsp;</p></div>
		<button class="collapsible" style="margin-bottom: 40px;">Friends list</button>
		<div class="content">
			<div class="center-in-column">
					<div id="top_right" class="inner-right">
						<h1>Friends</h1>
						<table style="width: 100%;">
						';
							if (count($list_of_friends) > 0){
								foreach ($list_of_friends as $friend){
									$username = $friend['username'];
									$id_val = $friend['user_id'];
									echo "<tr>";
									echo '<td><svg data-jdenticon-value="' . $username . '" width="35" height="35" style="background: white; margin-left: 7px; margin-top: 5px; border-radius: 50%;">Fallback text or image for browsers not supporting inline svg.</svg></td>';
									echo "<td>$username</td><td align=\"center\">
									";
									
									$blocked_by_arr = user_is_blocked($friends_id_str);
									
									if (in_array($id_val, $blocked_by_arr)){
										echo "
											You have been blocked
											";
									}else{
									
									if (count($blocked) >= 1){										
										if (in_array($id_val, $blocked)){
											echo "
											User Blocked
											</td>
												<td align=\"center\"><button class=\"button\"><a href=\"../users/unblock.php?to_block_id=$id_val\">Unblock user</a></button></td>
											</tr>";
										}
										else {
											echo "<button class=\"button\"><a href=\"../users/send_item.php?friends=$id_val\">Send Message</a></button>
											</td>
												<td align=\"center\"><button class=\"button\"><a href=\"../users/block.php?to_block_id=$id_val\">Block</a></button></td>
											</tr>";
										}
									}
									else {
										echo "<button class=\"button\"><a href=\"../users/send_item.php?friends=$id_val\">Send Message</a></button>
										</td>
											<td align=\"center\"><button class=\"button\"><a href=\"../users/block.php?to_block_id=$id_val\">Block</a></button></td>
										</tr>";
									}
								}
								}
							}
						  echo
						'
						</table>
					</div>
					<form action="" method="post">
					<div id="middle_right" class="send-item-right" style="display: none">
						<h3 style="float: left; width: 150px; height: 100px; margin-top: 10px">Send to Random Friend</h3>
							
							<a href="../users/send_item.php?friends=' . $friends_id_str . '">							
								<img id="send_item_image" src="img/send_item_small.png"/>
							</a>						
						</form>
					</div>
				</div>
			</div>
			</div>
		<div id="middle">
		<div class="center-description" id="center-description"><p class="center-in-div" style="color: white; margin-left: 46%;">Interactions</p></div>
		<button class="collapsible">Interactions</button>
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
 <!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div id="myProgress">
  <div id="myBar">0%</div>
</div>

<br>
	<button onclick="move()">Scan</button> 
</div>

</div>
 
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
	reloadRank();

	var timeout_timeline = setInterval(reloadTimeline, 2500); // update after 2.5s
	function reloadTimeline(){
		$(\'#timeline-container\').load(\'script/load_timeline.php\');
	}
    
	//var timeout = setInterval(recover, 3000); // update after 3s
	//function recover(){
	//	$(\'#cmVjb3Zlcg\').load(\'script/recover_user.php\');
	//}
	
	var timeout = setInterval(reloadRank, 2500); // update after 2.5s
	function reloadRank(){
		$(\'#running-score\').load(\'script/running_rank.php\');
	}
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

// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("av_btn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
  reset_bar();
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
	reset_bar();
  }
}
function move() {  
  var elem = document.getElementById("myBar");   
  var width = 0;
  var id = setInterval(frame, 10);
  function frame() {
    if (width >= 100) {
      clearInterval(id);
    } else {
      width++; 
      elem.style.width = width + \'%\';
      elem.innerHTML = width * 1  + \'%\';
	  if (width >= 100){
		  clearInterval(id);
			$.ajax({
				type: "POST",
				url: "script/antivirus.php",
				success: function(res) {
					if(res==="Success"){
						window.location.replace("index.php");
					}
				}
			});    
		} 
    }
  }
 
}
function reset_bar(){
	var elem = document.getElementById("myBar");   
	elem.style.width = 0 + \'%\'; 
	elem.innerHTML = 0 * 1  + \'%\';
}
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
</script>
</body>
</html>';

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

function get_username($user_id){
	global $conn;
	$query = "SELECT * FROM `user` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("i", $user_id );
	}

	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			return $row["username"];
		}
	}
}

$conn->close();
?>