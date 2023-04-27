<?php
require ("users/connect.php");

$error = $_GET["login_error"];

echo '
<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<html>
<head>
<title>Login</title>
	<link rel="stylesheet" media="screen" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="users/styles/css/login_form.css">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	
	<style>
		input[type=text]:-moz-read-only { /* For Firefox */
		  background-color: #263238;
		}

		input[type=text]:read-only {
		  background-color: #263238;
		}
	</style>
</head>

<body>
<div class="modal fade" id="modalRegisterForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="background-color: #263238;">
      <div class="modal-header text-center" style="background-color: #607D8B;">
        <h4>Sign up</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  
	  <form name="register_form" action="register.php" method="post" onsubmit="return validate_form()"> 
		  <div class="modal-body mx-3">
			<div class="md-form mb-5">
				<i class="fas fa-user prefix grey-text" style="color: white"></i>
				<input name="username" id="username" type="text" class="form-control validate" required><span id="user-result"></span>				
			  <!--<input name="username" id="username" type="text" class="form-control validate" value="' . generate_username() . '" readonly><span id="user-result"><img src="users/img/success.png" id="username_success" style="margin-left: 6px; width: 20px;"/></span>-->
			  <label data-error="wrong" data-success="right" for="orangeForm-name" style="color: white">Username</label>
			</div>
			<div class="md-form mb-4">
			  <i class="fas fa-lock prefix grey-text" style="color: white"></i>
			  <input type="password" id="txtNewPassword" class="form-control validate" required>
			  <label data-error="wrong" data-success="right" for="orangeForm-pass" style="color: white">Your password</label>
			</div>
			<div class="md-form mb-4">
			  <i class="fas fa-lock prefix grey-text" style="color: white"></i>
			  <input name="password" type="password" id="txtConfirmPassword" class="form-control validate" required>
			  <label data-error="wrong" data-success="right" for="orangeForm-pass" style="color: white">Confirm Password</label>
			</div>
			<div class="md-form mb-4">
			  <i class="fa fa-user-secret" style="color: white"></i>
			  <input name="secret_key" type="text" id="secret_key" class="form-control validate" required>
			  <label data-error="wrong" data-success="right" for="orangeForm-pass" style="color: white">Secret key</label>
			</div>
		  </div>
		  		  
		  <!--Footer of the modal-->
		  <div class="modal-footer d-flex justify-content-center">
			<input type="submit" style="width: 100%;" value="Sign up">
				<div class="registrationFormAlert" id="divCheckPasswordMatch"></div>
				<p style="color: red;" id="error"></p>
		  </div>	  
	  </form>
    </div>
  </div>
</div>

<div class="login">
	<!--<h2 style="color: white"><b>N</b>etwork dynam<b>IC</b>s evaluati<b>O</b>n p<b>L</b>ayer g<b>A</b>me</h2>-->
	<h2 style="color: white"><b>N</b>etwork eval<b>U</b>a<b>T</b>ion <b>M</b>ulti play<b>E</b>r <b>G</b>ame</h2>
	<img src="users/styles/logo.png" style="height: 30vh; width: 30vh"/>
	<form action="login.php" method="post">
	  <label for="uname" style="color: white"><b>Username</b></label>
	  <input type="text" name="username" placeholder="Login" required>
	  <label for="psw" style="color: white"><b>Password</b></label>
	  <input type="password" name="password" placeholder="Password" required>
	  <input type="submit" value="Login">
	</form>
	<div class="text-center">
	  <a href="" data-toggle="modal" data-target="#modalRegisterForm">Create Account</a>
	</div>
<div>

<script type="text/javascript">
$(document).ready(function() {
	var x_timer; 	
	$("#username").keyup(function (e){
		clearTimeout(x_timer);
		var user_name = $(this).val();
		x_timer = setTimeout(function(){
			check_username_ajax(user_name);
		}, 1000);
	});	

function check_username_ajax(username){
	$("#user-result").html(\'<img src="ajax-loader.gif" />\');
	$.post(\'username-checker.php\', {\'username\':username}, function(data) {
	  $("#user-result").html(data);
	});
}
});
</script>
<script>

$("#txtConfirmPassword").keyup(function() {
	var password = $("#txtNewPassword").val();
	$("#divCheckPasswordMatch").html(password == $(this).val()
		? "<p id=\"password_success\" style=\"color:green;\">Passwords match.</p>"
		: "<p id=\"password_error\" style=\"color:red;\">Passwords do not match!</p>"
	);
});
	
</script>
<script>
function validate_form() {
	var username, password, text, valid;
	
	valid = true;
	text = "";
	
	username = document.getElementById("username_success");
	password = document.getElementById("password_success");
	
	if (username == null){
	  text = "Username Error";
	  valid = false;
	}
	if (password == null){
		text = "Password Error";
		valid = false;
	}	
	
	document.getElementById("error").innerHTML = text;
	return valid;
}
</script>
</body>
</html>
';

if ($error){
	echo '<p style="color: #f00">Invalid username or password</p>';
}

function generate_username(){
	global $conn;
	
	$username = "unable_to_generate";
	
	$query = "SELECT * FROM user WHERE username LIKE '%user%' ORDER BY user_id DESC LIMIT ?";
	
	if ($stmt = $conn->prepare($query)) {
		/* bind parameters for markers */
		$stmt->bind_param("i", $i=1);
	}
	
	$result = select_sql($stmt);
	
	if ($result->num_rows >0){
		while ($row = $result->fetch_assoc()){
			 $username = $row["username"];
		}
	}
	
	$current_num = (int)substr($username, 4);
	srand(time());
	$new_num = mt_rand($current_num, $current_num+1000);
	
	$username = "user" . $new_num;
	
	return $username;
}
?>
