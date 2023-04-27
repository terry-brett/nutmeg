<?php

	$timer = 3*60; // seconds
	$timestamp_file = 'end_timestamp.txt';

	if (file_exists($timestamp_file)){
		$end_timestamp = file_get_contents($timestamp_file);
	}
	$current_timestamp = time();
	$difference = $end_timestamp - $current_timestamp;

	if($difference <= 0)
	{
		//unlink("../start");
		//unlink("end_timestamp.txt");
		//unlink("end_timestamp_score.txt");
		unlink_files();
		$score_timer = 1*90;
		$score_timestamp = 'end_timestamp_score.txt';
		if(!file_exists($score_timestamp))
		{
		  file_put_contents($score_timestamp, time()+$score_timer);
		}
	  sleep(2);
      echo "<script>window.location.replace(\"score.php\");</script>";
	  // execute your function here
	  // reset timer by writing new timestamp into file
	}
	else
	{
	  echo '<p class="nav">Time left in round: ' . gmdate("i:s", $difference).'s </p>';
	}
	
	function unlink_files(){
		$f1 = "../start";
		if (file_exists($f1)){
			unlink($f1);
		}
		
		$f2 = "end_timestamp.txt";
		if (file_exists($f2)){
			unlink($f2);
		}
		
		$f3 = "end_timestamp_score.txt";
		if (file_exists($f3)){
			unlink($f3);
		}
	}
?>