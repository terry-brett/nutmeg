<?php
require ("../connect.php");

$round_num = file_get_contents("../admin/round.txt");

$query = "SELECT * FROM `round_score` WHERE `round_num` = " . $round_num . " ORDER BY total DESC";
$stmt = $conn->prepare($query);
$result = select_sql($stmt);

session_start(); // start session to get username 

$my_id = $_SESSION["user_id"];

echo '<table style="width: 100%; border: 1px solid #607D8B;">
<caption><b>Rank</b></caption>
<tr>';
if ($round_num == 4){
		echo '
			<th>Position</th>
			<th>Score</th>
		';
}

echo '
</tr>
';



$count = 1;

if ($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$user_id = $row["user_id"];
		$user_total = $row["total"];
		
		if ($user_id == $my_id){
			echo '
				<tr>
				<td align="center">';
				  
				  echo addOrdinalNumberSuffix($count);
				  
				  if ($round_num == 3){
					  echo '
						</td>
						<td align="center">' . $user_total;
				  }
				  
				echo '				
				</td>
				</tr>				
			';
		}
		else {
			$count += 1;
		}
	}
}

echo '</table>';

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