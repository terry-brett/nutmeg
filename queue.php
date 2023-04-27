<?php

echo '
<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<html>
<head>
<title>Please Wait...</title>
	<link rel="stylesheet" media="screen" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="users/styles/css/login_form.css">
	<link rel="stylesheet" href="users/styles/css/main.css">
	<link rel="stylesheet" href="users/styles/css/home_page.css">
</head>
<style>
.content {
  max-width: 500px;
  margin: auto;
  padding: 10px;
  margin-top: 70px;
}
</style>
<body>

<div class="content">
	<h1 style="weight: bold; color: white;">Welcome to the Experiment!</h1>
	<h2 style="weight: bold; color: white;">Please wait for the round to begin</h2>
	<img style="margin-left: 20%" src="users/img/09b24e31234507.564a1d23c07b4.gif"/>
</div>

<div id="start"></div>
<div id="description"></div>
<script>
	gameStarted();
	var timeout = setInterval(gameStarted, 100); 
	function gameStarted () {
     $(\'#start\').load(\'users/script/game_started.php\');
	}
	
	get_next_level();
	var time = setInterval(get_next_level, 100);
	function get_next_level () {
		$(\'#description\').load(\'users/script/get_round.php\');
	}
</script>
</body>
</html>
';
?>