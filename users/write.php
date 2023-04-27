<?php

	$round_num = file_get_contents("admin/round.txt");
	
	if ($round_num == 2){
		$f = fopen('2opened', 'w+');
		fwrite($f, "");
		fclose($f);
	}
	else if ($round_num == 3){
		$f = fopen('3opened', 'w+');
		fwrite($f, "");
		fclose($f);
	}
	else if ($round_num == 4){
		$f = fopen('4opened', 'w+');
		fwrite($f, "");
		fclose($f);
	}
	
   
?>