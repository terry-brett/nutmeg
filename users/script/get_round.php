<?php
$round_num = file_get_contents("../admin/round.txt");

$timestamp_file = 'end_timestamp_score.txt';
$end_timestamp = file_get_contents($timestamp_file);
$current_timestamp = time();
$difference = $end_timestamp - $current_timestamp;
if (file_exists($timestamp_file)){
	if($difference > 2){
		 echo "<script>window.location.replace(\"users/score.php\");</script>";
	}
}
		

echo '
	<p style="color: white; margin-left: 750px"><strong>Next round: ' . $round_num . '</strong></p>';


if ($round_num == 4){
	echo '
	<div class="inner-left" style="width: 396px; margin-left: 750px"">
		<br>
		<p><strong>Scenario:</strong></p>
		<p style="text-align: left">In this scenario you are allowed to interact with a preset list of friends. Here you can only send a message at random to one of your friends, and you will also be chosen at random on their side. The message you receive might be potentially compromised.</p><br>
	</div>
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Score:</strong></p>
		<p style="text-align: left">The score is calculated by considering the number of messages you send and you open, and by deducting the number of compromised message you send and you open.</p>
		<br>
	</div>
';
}
else if ($round_num == 1){
	echo '
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Scenario:</strong></p>
		<p style="text-align: left">In this scenario you will be presented with a list of friends. You can choose a friend to send a message to, and you will see all the messages recieved in your timeline.</p><br>
	</div>
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Score:</strong></p>
		<p style="text-align: left">The score is calculated by considering the number of messages that your friends have opened, which originated from you and by deducting the number of compromised message you send and you open. You will also recieve bonus points if you remove a message that is compromised.</p>
		<br>
	</div>
	';
}
else if ($round_num == 2){
	echo '	
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Scenario:</strong></p>
		<p style="text-align: left">In this scenario you will know who has infected you after opening the message. You should be aware of this, as your score will be penalised and you will be sending out infected messeages to your friends!</p><br>
	</div>
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Score:</strong></p>
		<p style="text-align: left">The score is calculated by considering the number of messages that your friends have opened, which originated from you and by deducting the number of compromised message you send and you open. You will also recieve bonus points if you remove a message that is compromised.</p>
		<br>
	</div>
	';
}
else if ($round_num == 3){
	echo '	
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Scenario:</strong></p>
		<p style="text-align: left">In this scenario you can now block people, which will mean you will not longer be able to exchange messages. You can also use a virus scanner, which will remove the virus, you can use this as many times as you want. Using the antivirus will cost you 10 points</p><br>
	</div>
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Score:</strong></p>
		<p style="text-align: left">The score is calculated by considering the number of messages that your friends have opened, which originated from you and by deducting the number of compromised message you send and you open. You will also recieve bonus points if you remove a message that is compromised. <strong style="color: yellow">Using antivirus will cost you 10 points</strong></p>
		<br>
	</div>
	';
}
else {
  echo '
	<div class="inner-left" style="width: 396px; margin-left: 750px">
		<br>
		<p><strong>Round is being prepared</strong></p>
		<p style="text-align: left">Please wait while the round is set up by host</p>
		<br>
	</div>
';
}

?>