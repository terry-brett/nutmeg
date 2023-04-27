<?php
require ("../connect.php");

session_start();

$user_id = $_SESSION["user_id"];

$gift = glob('../../items/' . $user_id . '_*.json');

$number_of_items = count($gift);

if ($number_of_items >= 1){
	echo "<script>
		var elem = document.getElementById('gifts');
		elem.style.display = 'block';</script>";
}
else {	
echo "<script>
	var elem = document.getElementById('gifts');
	elem.style.display = 'none';</script>";
}

$number_of_items_in_inventory = get_num_of_items_in_inventory($user_id);

echo "<table class=\"gift-table\" style=\"width: 100%;\">
	<tr>
    <th>Gift</th>
  </tr>
";
foreach ($gift as $item){
	$filename = explode("/", $item);
	// need from_id and the filename to open the item
	$from_id = explode("(", explode(".", explode("_", $filename[3])[1])[0])[0];
	$filename = explode("/", $item)[3];
	
	if ($number_of_items_in_inventory >= 10){
		echo '
			<div class="icon-container">
				<a href="#" onClick="alert(\'Cannot open item! Inventory full!\')"><div><img src="img/gift_small.png"/></div></a>
			</div>
		';
	}
	else {
		echo '
			<div class="icon-container">
				<a href="script/open_item_blank.php?filename=' . encrypt($filename) . '&from_id=' . encrypt($from_id) . '"><div class="tooltip"><img src="img/gift_small.png"/></div></a>
			</div>
		';
	}
	if ($number_of_items_in_inventory >= 10){
		echo '<tr><td align="center"><a href="#" onClick="alert(\'Cannot open item! Inventory full!\')"><img src="img/gift_small.png"/></a></td></tr>';
	}
	else {
		echo '<tr><td align="center"><a href="script/open_item.php?filename=' . encrypt($filename) . '&from_id=' . encrypt($from_id) . '"><img src="img/gift_small.png"/></a></td></tr>';
	}
}

echo "</table>";

// get timeline details

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

function get_username($item){
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

function get_num_of_items_in_inventory($user_id){
	global $conn;
	$query = "SELECT * FROM `user_score` WHERE `user_id`=?";
	
	if ($stmt = $conn->prepare($query)) {
	/* bind parameters for markers */
		$stmt->bind_param("i", $user_id);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$items_arr = $row["item_inventory"];
		}
	}
	if (is_null($items_arr)){
		return 0;
	}
	
	$count = count(unserialize($items_arr));
	return $count;
}

$conn->close();
?>