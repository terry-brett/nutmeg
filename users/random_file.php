<?php
require ("connect.php");

session_start();

$user_id = $_SESSION["user_id"];	

$content_str = "";
$friends_id_str = $_POST['friend_list'];

$file = get_random_file();
$ext = pathinfo($file, PATHINFO_EXTENSION);
if ($ext == "mp4" || $ext == "mov" || $ext == "vob" || $ext == "mpeg" || $ext == "3gp" || $ext == "avi" || $ext == "wmv" || $ext == "mov" || $ext == "amv" || $ext == "svi" || $ext == "flv" || $ext == "mkv" || $ext == "webm" || $ext == "gif" || $ext == "asf") {
    $content_str .= "
	<style>
	.modal-content{
		width: 80%;
	}
	</style>
	<div class='flowplayer' data-swf='flowplayer.swf' data-ratio='0.4167'>";
    $content_str .= "<video style=\"margin-left: 20%\" controls width='1000px'>";
    $content_str .= "<source type='video/mp4' src='$file' autoplay='autoplay'>";
    $content_str .= "<source type='video/ogg' src='$file' autoplay='autoplay'>";
    $content_str .= "</video>";
    $content_str .= "</div>";
} 
elseif (filter_var($file, FILTER_VALIDATE_URL)){
    $content_str .= $file;
}
elseif (strpos($file, 'Stim-02-image')){ // Stim-02-image
	$content_str .= "
	<style>
	.modal-content{
		width: 80%;
		overflow:scroll;
	}
	</style>
	<img style=\"margin-left: 300px\" src='" . $file. "' class='img-polaroid' alt='Img' width:='700px' height='700px'>";
}
else {
	$content_str .= "
	<style>
	.modal-content{
		width: 80%;
	}
	</style>
	<img style=\"margin-left: 100px\" src='" . $file. "' class='img-polaroid' alt='Img' width:='800px'>";
}

$i = user_is_infected();
if ($i == true){
	$i = 1;
}
else {
	$i = 0;
}

echo '
<div id="myModal" class="modal" style="display: block">

  <div class="modal-content">
	<div>' . $content_str . '</div>

<br>
<button onclick="location.href=\'send_item.php?friends=' . $friends_id_str . '&file=' . $file . '\'" style="margin-left: 50px">Send</button> 
<button onclick="location.href=\'dont_send.php?friends=' . $friends_id_str . '&file=' . $file . '&infected=' . $i . '\'" style="margin-left: 50px">Don\'t Send</button> 
</div>
</div>
';

function user_is_infected(){
	global $conn, $user_id;
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

function get_random_file(){
	 $type = "safe";
	 if (user_is_infected()){
		$type = "infected";
	 }	 
	
	 $directories = glob('content/' . $type . '/*');
	 $directory = array_rand($directories);
	 $new_dir = $directories[$directory];
	 if (strpos($new_dir, 'links')){
		return get_link($type);
	 }
	 else {
		 $files = glob($new_dir . '/*.*');
		 $file = array_rand($files);
		 return $files[$file];
	 }
}

function get_link($type){
	$dir = "content/$type/links/sites.txt";
	$lines = file($dir, FILE_IGNORE_NEW_LINES);
	return $lines[array_rand($lines)];
}

?>