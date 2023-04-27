<?php
require ("../connect.php");
session_start();
$user_id = $_SESSION["user_id"];
$query = "SELECT * FROM `timeline` WHERE `user_id`=?";
$gift = glob('../../items/' . $user_id . '_*.json');
$number_of_items = count($gift);
usort($gift, create_function('$b,$a', 'return filemtime($b) - filemtime($a);')); // sort the files in reverse chronological order
if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
    $stmt->bind_param("i", $user_id );
}
$result = select_sql($stmt);
$timeline = array();
// recieved_from id who it came from as string
if ($result->num_rows > 0){
	// fetch the data from DB
	while ($row = $result->fetch_assoc()){
		array_push($timeline, array($row["recieved_from"], $row["sent_to_user"], $row["recieved_from_user"]));
	}
}
echo '
<style>
.tooltip {
  position: relative;
  display: inline-block;
}
.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: black;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  
  /* Position the tooltip */
  position: absolute;
  z-index: 1;
  bottom: 100%;
  left: 50%;
  margin-left: -60px;
}
.tooltip:hover .tooltiptext {
  visibility: visible;
}
</style>
';
$i = 1;
foreach ($gift as $item){
	$filename = explode("/", $item);
	// need from_id and the filename to open the item
	$from_id = explode("(", explode(".", explode("_", $filename[3])[1])[0])[0];
	$filename = explode("/", $item)[3];
	$item_id = explode(".", $filename)[0];
	
	$json_data = file_get_contents('../../items/' . $filename);
	$json = json_decode($json_data);
	$file  = $json->loc;
	
	$content_str = "";
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	if ($ext == "mp4" || $ext == "mov" || $ext == "vob" || $ext == "mpeg" || $ext == "3gp" || $ext == "avi" || $ext == "wmv" || $ext == "mov" || $ext == "amv" || $ext == "svi" || $ext == "flv" || $ext == "mkv" || $ext == "webm" || $ext == "gif" || $ext == "asf") {
		$content_str .= "<div class='flowplayer' data-swf='flowplayer.swf' data-ratio='0.4167'>";
		$content_str .= "<video style=\"margin-left: 40%\" controls width='200px'>";
		$content_str .= "<source type='video/mp4' src='$file' autoplay='autoplay'>";
		$content_str .= "<source type='video/ogg' src='$file' autoplay='autoplay'>";
		$content_str .= "</video>";
		$content_str .= "</div>";
	} 
	elseif (filter_var($file, FILTER_VALIDATE_URL)){
		$content_str .= $file;
	}
	else {
		$content_str .= "<img style=\"margin-left: 40%\" src='" . $file. "' class='img-polaroid' alt='Img' width='200px'>";
	}
	
	echo '
	<div class="inner">
		<div class="container">
			<div class="column" style="width: 70%;">
				<table>
					<tr>
						<td style="padding:0 15px 0 15px;">
							<p> Message received</p>
						</td>
					</tr>
				</table>
			   
				
			</div>
			<div id="message_icon" class="column" style="width: 15%;">
				<div class="text-container">
					<a class="btn" id="open_message_btn' . $i . '" style="text-decoration:none; margin-top: -40px" href="#">View</a>
				</div>
			</div>
		</div>
	</div>
	
		<!-- Remove Item Modal -->
	<div id="open_item_modal' . $i . '" class="modal">
		<div class="modal-content">
			<span onclick="document.getElementById(\'open_item_modal' . $i . '\').style.display=\'none\'" class="close">&times;</span>
			<p style="margin-left: 20px">' . $content_str . '</p><br>
			<button onclick="location.href=\'script/open_item.php?filename=' . encrypt($filename) . '&btn_type=likely\';" style="margin-left: 20px">Likely an attack</button> 
			<button onclick="location.href=\'script/open_item.php?filename=' . encrypt($filename) . '&btn_type=not_likely\';" style="margin-left: 20px">Not likely an attack</button> 
			<!--<button onclick="reply(' . $from_id . ', \'' . $item_id . '\', \'' . encrypt($filename) . '\')" style="margin-left: 70px; margin-bottom: 10px">Not likely an attack</button>-->
			<!--<button onclick="remove(\'' . $item_id . '\')" style="margin-left: 70px; margin-bottom: 10px">Delete</button>-->
		</div>
	</div>
	
	<script>	
		var open_btn = document.getElementById("open_message_btn' . $i . '");
		open_btn.onclick = function() {
		  clearInterval(timeout_timeline);
		  open_item_modal' . $i . '.style.display = "block";
		}
		
		var item_id = null;
		function remove_item(item){
			open_item_modal' . $i . '.style.display = "block";
			item_id = item;
		}
		function reply(id, item_id, f){
			$.ajax({
				type: "POST",
				url: "send_item.php?friends=" + id,
				success: function(res) {
					if(res==="Success"){
						window.location.replace("index.php");
					}
					else {
						open(f);
						remove(item_id);
						window.location.replace("index.php");
					}
				}
			});
		}
		function open(f){
			$.ajax({
				type: "POST",
				url: "script/open_item.php?filename=" + f,
				success: function(res) {
					if(res==="Success"){
						window.location.replace("index.php");
					}
				}
			});
		}
		function remove(id){
			$.ajax({
				type: "POST",
				url: "script/remove_item.php?itemid=" + id,
				success: function(res) {
					if(res==="Success"){
						window.location.replace("index.php");
					}
				}
			});
		}
		window.onclick = function(event) {
		  if (event.target == open_item_modal' . $i . ') {
			open_item_modal' . $i . '.style.display = "none";
		  }
		}
	</script>
	';
	$i += 1;
}
/*
for ($i = 0; $i < sizeof($timeline); $i += 1) { 	
	echo '
	<div class="inner">
		<div class="container">
			<div class="column" style="width: 70%;">
				<table>
					<tr>
						<td><svg data-jdenticon-value="' . get_username($timeline[$i][0]) . '" width="70" height="70" style="background: white; margin-left: 7px; margin-top: 5px; border-radius: 50%">Fallback text or image for browsers not supporting inline svg.</svg></td>
						<td style="padding:0 15px 0 15px;">
							<p><strong>Message from  ' . get_username($timeline[$i][0]) . ' </strong></p>
							<p> Items sent: ' . $timeline[$i][1].'</p>
							<p> Items received: ' . $timeline[$i][2] . '</p>
						</td>
					</tr>
				</table>
			   
				
			</div>
			<div id="message_icon" class="column" style="width: 15%;">
				<div class="text-container">
					<a class="btn" style="text-decoration:none;" href="#">Open</a>
				</div>
			</div>
		</div>
		<div class="tooltip">
			<img src="img/reply_icon.png" style="width: 20px; margin-left: 100px"/>
			<span class="tooltiptext" style="margin-left: -9px">Reply</span>
		</div>
		<div class="tooltip">
			<img src="img/remove_icon.png" style="width: 20px; margin-left: 40px"/>
			<span class="tooltiptext" style="margin-left: -40px">Delete</span>
		</div>
	</div>';
}*/
echo '
<script>
	function switch_tab(){
		document.getElementById("right-span").click(); // Click on the checkbox
	}
</script>
</script>
  <script src="https://cdn.jsdelivr.net/npm/jdenticon@2.1.1" async></script>
<script>
';
function get_item_username($item){
	global $conn;
	$filename = explode("/", $item);
	$from_user_id = explode("(", explode(".", explode("_", $filename[3])[1])[0])[0];
	
	$query = "SELECT `username` FROM `user` WHERE `user_id`=?";
	if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
		$stmt->bind_param("i", $from_user_id);
	}
	$result = select_sql($stmt);
	if ($result->num_rows > 0){
		// fetch the data from DB
		while ($row = $result->fetch_assoc()){
			return $row["username"]; // get the list of friends (CSV) into an array
		}
	}
	
	return $from_user_id;
}
function get_item_received($user_id, $from_id){
	global $conn;
	$query = "SELECT * FROM `timeline` WHERE `user_id`=? AND `recieved_from`=?";
	
	if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
		$stmt->bind_param("ii", $user_id, $from_id);
	}
	
	$result = select_sql($stmt);
	
	$timeline = array();
	if ($result->num_rows > 0){
	// fetch the data from DB
		while ($row = $result->fetch_assoc()){
			array_push($timeline, $row["sent_to_user"], $row["recieved_from_user"]);
		}
	}
	
	return $timeline;
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
function count_items($to_id, $from_id){
	$gift = glob('../../items/' . $to_id. '_' .$from_id . '*.json');
	return count($gift);
}
function encrypt($string, 
$key = 'MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAIMpvz+6pqAK/tQrUwC3l2Tma1zsojSOzvee0fmP9uqa4fcUUwDZjOhUDQg/ig0TX38mnSBK10ItHeq422+grVECAwEAAQ==', 
$secret = 'MIIBOwIBAAJBAIMpvz+6pqAK/tQrUwC3l2Tma1zsojSOzvee0fmP9uqa4fcUUwDZjOhUDQg/ig0TX38mnSBK10ItHeq422+grVECAwEAAQJAJr0BuzTJWaNluAxDq4aNtENJmlxZW+SBxCioI2kdqBQ9lnRP5fMm6Bwm5woqpMd2uOjaEVTeCwAYBHMGznV4AQIhAPWWue+I79j9VPkktHrX7WDrbietFPh+3oOjHF8L8sHhAiEAiLk0sK12u3CkoDMYjdHSGJnB1hNqkgBvanKia8IOOXECIQDm9Y39L/noRi5gc91rXZ/3MtGQbJy5KY8XmxD2beUp4QIgWK85nDiIQYEJZ9h83tDw5IAnmUKy581cd8Gv1RHkxCECIQCEvJDEFl1WYDk7KwwF8HWrB2mk+YZF7BEPpOJbpHtpJA==', 
$method = 'AES-256-CBC') {
    // hash
    $key = hash('sha256', $key);
    // create iv - encrypt method AES-256-CBC expects 16 bytes
    $iv = substr(hash('sha256', $secret), 0, 16);
    // encrypt
    $output = openssl_encrypt($string, $method, $key, 0, $iv);
    // encode
    return base64_encode($output);
}
$conn->close();
?>