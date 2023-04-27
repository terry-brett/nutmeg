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
		#highlight_box{
		 background-color : gray;
		 border : 5px solid yellow;
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
				<h1 style="margin: 0px" id="profile-header">Profile</h1>
				<!--<svg data-jdenticon-value="' . $username . '" width="70" height="70" style="background: white; margin-left: 7px; margin-top: 10px; border-radius: 50%">Fallback text or image for browsers not supporting inline svg.</svg>-->
				<img src="https://avatars.dicebear.com/v2/bottts/' . $username . '.svg" width="70" height="70" style="background: white; margin-left: 7px; margin-top: 10px; border-radius: 50%"/>
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
							<input id="av_btn" type="image" src="img/av_icon.png" style="width: 60px; margin-left: -10px;" onclick="open_av_modal()"/>
						</div>
						<div class="column" style="width: 80%;">
							<p>Click to remove virus</p>
						</div>
					</div>
				</div>
				
				<div id="score_dsc" class="inner-left" style="width: 48%">
					<br>
					<p><strong>Score:</strong></p>
					<p style="text-align: left">The score is calcuated by taking the number of messages you send and deducting a the number of compromised messages you open. A penalty for using the virus scanner will cost you 10 points</p>
					<br>
				</div>
				<div id="round_dsc" class="inner-left" style="width: 48%">
					<br>
					<p><strong>Scenario:</strong></p>
					<p style="text-align: left">In this scenario you can now block people, which will mean you will not longer be able to exchange messges. You can also use a virus scanner, which will remove the virus, you can use this as many times as you want. </p><br>
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
									#echo '<td><svg data-jdenticon-value="' . $username . '" width="35" height="35" style="background: white; margin-left: 7px; margin-top: 5px; border-radius: 50%;">Fallback text or image for browsers not supporting inline svg.</svg></td>';
									echo '<td><img src="https://avatars.dicebear.com/v2/bottts/' . $username . '.svg" width="35" height="35" style="background: white; margin-left: 7px; margin-top: 5px; border-radius: 50%;"/></td>';
									echo "<td>$username</td><td align=\"center\">
									";
									
									if (is_blocked($user_id, $id_val)){
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
											echo "<button class=\"button\" id=\"btn$id_val\"><!--<a href=\"../users/send_item.php?friends=$id_val\">Send Message</a>-->Send Message</button>
											</td>
												<!-- <td align=\"center\"><button class=\"button\"><a href=\"../users/block.php?to_block_id=$id_val\">Block</a></button></td> -->
												<td align=\"center\"><button id=\"open_block_modal$id_val\" class=\"button\" onclick='block_modal$id_val.style.display = \"block\";'>Block</button></td>
											</tr>";
											echo '
											<script>
											  $(document).on("click", "#btn' . $id_val . '", function(){
													var clickBtnValue = $(this).val();
													var ajaxurl = \'random_file.php\',
													data = {\'friend_list\' : "' . $id_val . '"};
													$.post(ajaxurl, data, function (response) {
														// Response div goes here.
													   document.body.innerHTML = document.body.innerHTML + response;
													});
												});
											</script>
											';
										}
									}
									else {
										echo "<button class=\"button\" id=\"btn$id_val\"><!--<a href=\"../users/send_item.php?friends=$id_val\">Send Message</a>-->Send Message</button>
										</td>
											<!-- <td align=\"center\"><button class=\"button\"><a href=\"../users/block.php?to_block_id=$id_val\">Block</a></button></td> -->
											<td align=\"center\"><button id=\"open_block_modal$id_val\" class=\"button\" onclick='block_modal$id_val.style.display = \"block\";'>Block</button></td>
										</tr>";
										echo '
										<script>
										  $(document).on("click", "#btn' . $id_val . '", function(){
												var clickBtnValue = $(this).val();
												var ajaxurl = \'random_file.php\',
												data = {\'friend_list\' : "' . $id_val . '"};
												$.post(ajaxurl, data, function (response) {
													// Response div goes here.
												   document.body.innerHTML = document.body.innerHTML + response;
												});
											});
										</script>
										';
									}
								}
								
								echo '
								<div id="block_modal' . $id_val . '" class="modal">
									<div class="modal-content">
										<span onclick="document.getElementById(\'block_modal' . $id_val . '\').style.display=\'none\'" class="close">&times;</span>
										<p style="margin-left: 10px;">If you block a user, they will not be able to send messages to you and vice versa. Block & Remove will block the user and remove all messages that you have recived from them.</p><br>
										<button onclick="location.href=\'../users/block.php?to_block_id=' . $id_val . '\';" style="margin-left: 20px">Block</button> 
										<button onclick="location.href=\'../users/block.php?block_and_remove=' . $id_val . '\';" style="margin-left: 50px">Block & Remove</button> 
									</div>
								</div>
								';
								
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
 <!-- The Modal -->
<div id="av_modal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span onclick="document.getElementById(\'av_modal\').style.display=\'none\'" class="close">&times;</span>
	<p style="margin-left: 10px;">This will remove the virus <strong style="color: red">but it will cost you 10 points. This only works if you are infected!</strong></p>
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
	recover();
	reloadRank();

	var timeout_timeline = setInterval(reloadTimeline, 1500); // update after 2.5s
	function reloadTimeline(){
		$(\'#timeline-container\').load(\'script/load_timeline.php\');
	}
    
	var timeout = setInterval(recover, 3000); // update after 3s
	function recover(){
		$(\'#cmVjb3Zlcg\').load(\'script/recover_user.php\');
	}
	
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
	}';
	if (!file_exists("4opened")){
		echo '
		setTimeout(function(){
		$("[id*=\'highlight_box\']").removeAttr(\'id\');
		$.ajax({
				type: "POST",
				url: "write.php",
				success: function(res) {
					if(res==="Success"){
						window.location.replace("index.php");
					}
					else {
						window.location.replace("index.php");
					}
				}
			});
	},2000);
		';
	}
	else {
		echo '$("[id*=\'highlight_box\']").removeAttr(\'id\');';
	}
	
echo '
</script>
  <script src="https://cdn.jsdelivr.net/npm/jdenticon@2.1.1" async></script>
<script>

function open_av_modal(){
	// Get the modal
	var av_modal = document.getElementById("av_modal");
	av_modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
//span.onclick = function() {
  //modal.style.display = "none";
  reset_bar();
//}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == av_modal) {
    av_modal.style.display = "none";
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
</script>
</body>
</html>';

// regex for matching file = ^3_\d+(.*).json$

function is_blocked($user_id, $friend_id){
	global $conn;
	
	$query = "SELECT * FROM `friends` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)){
			$stmt->bind_param("i", $friend_id);
		}
	$result = select_sql($stmt);
	
	if ($result->num_rows >0){
			while ($row = $result->fetch_assoc()){
				 $l = $row["blocked_list"];
				 if (!is_null($l) and $l != ""){ // check block list is not empty
					if (strpos($a, ',') == true){ // check if it's a CSV
						$list = explode(",", $l);
						if (in_array($user_id, $list)){
							return True;
						}
					}
					else {
						if ($l == $user_id){
							return True;
						}
					}
				 }
			}
	}
	
	return False;
}

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
				 
				 array_merge($blocked_by, $list);
				 /*$list = explode(",", $l);
				 if (count($list) > 0){
					 if (!in_array($id, $list)){						 
						array_push($blocked_by, $id);
					 }
				 }*/
				 }

			}
		}
	}
	return $blocked_by;
}

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
