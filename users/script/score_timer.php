<?php

	$timer = 1*90; // seconds
	$timestamp_file = 'end_timestamp_score.txt';

	$end_timestamp = file_get_contents($timestamp_file);
	$current_timestamp = time();
	$difference = $end_timestamp - $current_timestamp;
	
	if (file_exists($timestamp_file)){
	if($difference <= 0)
		{
		  echo "<script>window.location.replace(\"../queue.php\");</script>";
		}
		else
		{
		  echo '<p class="nav">Next round starts in: ' . gmdate("i:s", $difference).'s </p>';
		}
	}
	
?>