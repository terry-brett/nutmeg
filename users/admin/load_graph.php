<?php
require ("../connect.php");
session_start();

$user_id = $_SESSION["user_id"];

$graph = base64_decode($_GET["graph"]);

$query = "SELECT * FROM `user`";
$stmt = $conn->prepare($query);
$result = select_sql($stmt);

$infection_array = array();

si();

if ($result->num_rows > 0){
	// fetch the data from DB
	while ($row = $result->fetch_assoc()){
		$temp_arr = [$row["user_id"], $row["user_infected"], $row["user_susceptible"]];
		array_push($infection_array, $temp_arr);
	}
}

$query = "SELECT * FROM `friends`";
$stmt = $conn->prepare($query);
$result = select_sql($stmt);

$edges_array = array();

if ($result->num_rows > 0){
	// fetch the data from DB
	while ($row = $result->fetch_assoc()){
		$temp_arr = [$row["user_id"], $row["list_of_friends"]];
		array_push($edges_array, $temp_arr);
	}
}


$color = " ";
$edge = " ";
foreach ($infection_array as $value) {
	// node_id, user_infected, user_susceptible
	//echo $value[0] . " " . $value[1] . " " . $value[2] . "<br>";
	$color .= build_node( $value[0], $value[1], $value[2]);
}

foreach ($edges_array as $edges){
	$edge .= build_edge($edges[0], $edges[1]);
}

function build_node($node_id, $user_infected, $user_susceptible){
	if ($node_id != 5){
		if (($user_infected == 0) and ($user_susceptible == 0)){
			return "G.addNode($node_id, {color: '#0F0'});";
		}
		if ($user_infected == 1){
			return "G.addNode($node_id, {color: '#F00'});";
		}
		else if ($user_susceptible == 1){
			return "G.addNode($node_id, {color: '#F4DC42'});";
		}
	}
}

function build_edge($from_node, $to_nodes_list){
	$nodes = str_getcsv($to_nodes_list);
	if ($from_node != 5){ // rempve admin
		$edges = "";
		foreach ($nodes as $n){
			$edges .= "G.addEdge($from_node, $n);";
		}
		return $edges;
	}
}

function si(){
	global $conn;
	if (file_exists("../start")){		
		$query = "SELECT COUNT(*) FROM `user` WHERE `user_infected`=1";	
		
		$stmt = $conn->prepare($query);
		$result = select_sql($stmt);
		while ($row = $result->fetch_assoc()){
			file_put_contents("si/" . time() . ".infected", $row['COUNT(*)']);
		}
		
		
		$query = "SELECT COUNT(*) FROM `user` WHERE `user_susceptible`=1";	
		
		$stmt = $conn->prepare($query);
		$result = select_sql($stmt);
		while ($row = $result->fetch_assoc()){
			file_put_contents("si/" . time() . ".susceptible", $row['COUNT(*)']);
		}
	}
}

//echo "<input type=\"text\" id=\"myText\" value=\"$color\">";

//echo $color;
//echo $edge;
echo "
<script>
function update(){
	$color
	$edge
}
</script>
";

$conn->close();
?>