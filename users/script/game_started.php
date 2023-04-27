<?php

$filename = '../start';
$timestamp_file = 'end_timestamp.txt';

if (file_exists($filename) and file_exists($timestamp_file)) {
	echo "<script>window.location.replace(\"users/index.php\");</script>";
}

?>