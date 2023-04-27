<?php

$dbOptions = array(
        'db_host' => 'localhost',
        'db_user' => 'pmauser',
        'db_pass' => 'ywUaEcSI',
        'db_name' => 'user_db'
    );
	
$conn = new mysqli($dbOptions['db_host'], $dbOptions['db_user'], $dbOptions['db_pass'], $dbOptions['db_name']) or die(mysqli_error());

function select_sql($statement){
	global $conn;
	//$conn = new mysqli($dbOptions['db_host'], $dbOptions['db_user'], $dbOptions['db_pass'], $dbOptions['db_name']) or die(mysqli_error());
	// Check connection
	if (mysqli_connect_errno()) {
		die("Connection failed: " . $conn->connect_error);
	}
	
	// Check for any SQL errors
	if (!$statement->execute()) {
		printf("Error message: %s\n $statement", $conn->error);
	}
	
	
	$statement->execute();
	//grab a result set
	$result_set = $statement->get_result();

	//pull all results as an associative array
	//$result = $result_set->fetch_assoc();
	
	return $result_set;
}

function insert_sql($statement){
	global $conn;

    //$mysqli = new mysqli($dbOptions['db_host'], $dbOptions['db_user'], $dbOptions['db_pass'], $dbOptions['db_name']);
    // Check connection
    if (mysqli_connect_errno()) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if statement executed properly
	// return true if executed fine, else return false
    if ($statement->execute() === TRUE) {
        return TRUE;
        //header("Location: main.php?poke_sent=true");
        //die();
    } else {
        //printf("Error message: %s\n $statement", $conn->error);
		return FALSE;
    }
}

function update_sql($statement){
	global $conn;
	
    //$mysqli = new mysqli($dbOptions['db_host'], $dbOptions['db_user'], $dbOptions['db_pass'], $dbOptions['db_name']);
    // Check connection
    if (mysqli_connect_errno()) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if statement executed properly
	// return true if executed fine, else return false
    if ($statement->execute() === TRUE) {
        return TRUE;
        //header("Location: main.php?poke_sent=true");
        //die();
    } else {
        //printf("Error message: %s\n $statement", $conn->error);
		return FALSE;
    }
}

?>
