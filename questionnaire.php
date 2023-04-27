<?php
require ("users/connect.php");

session_start(); 

$user_id = $_SESSION["user_id"];

$query = "SELECT * FROM `user` WHERE `user_id`=?";
if ($stmt = $conn->prepare($query)) {
	$stmt->bind_param("i", $user_id);
}
$result = select_sql($stmt);
if ($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$v = $row["questionnaire_completed"];
	}
}

if ($v == true){
	header("Location: queue.php");
}

echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
	<link rel="stylesheet" href="users/styles/css/main.css">	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<style>
		table {
		  font-family: arial, sans-serif;
		  border-collapse: collapse;
		  width: 100%;
		}

		td, th {
		  border: 1px solid #fff;
		  text-align: center;
		  padding: 8px;
		}
		#other_browser{
			align: center;
		}
	</style>
</head>
<body>
<div id="header">
<h1 align="center">Participant information sheet</h1>
<table style="width: 70%; margin: auto">
  <tr>
    <th>Research title: Understanding contagion spreading process of cyber security threats through social networks</th>
  </tr>
  <tr>
    <td>Contact</td>
  </tr>
  <tr>
    <table style="width: 70%; margin: auto">
        <tr>
          <td>Terry Brett</td>
          <td><a href="mailto:terry.brett@greenwich.ac.uk" style="color: white">terry.brett@greenwich.ac.uk</a></td>
        </tr>
        <tr>
          <td>Dr. Nicola Perra</td>
          <td><a href="mailto:n.perra@greenwich.ac.uk" style="color: white">n.perra@greenwich.ac.uk</a></td>
        </tr>
		<tr>
          <td>Dr. George Loukas</td>
          <td><a href="mailto:g.loukas@greenwich.ac.uk" style="color: white">g.loukas@greenwich.ac.uk</a></td>
        </tr>
      </table>
  </tr>
</table>
</div>
<br>
<div style="width: 70%; margin: auto" id="doc">
<p>
<b>Invitation.</b> You are invited to take part in this research project. Before you decide to do so, it’s important to understand what you will be doing and what it will involve. Please take time to read the information included in here, and do not hesitate to contact us for any further information, if there is anything that might not be clear or if you would like more information.
</p>

<p>
<b>Purpose of the research.</b> This research focuses on the contagion spread of malicious content via social networks. Rather than focusing on the features of malicious content, we are looking at the social factors of it. Previous research focuses on the features of cyber threats (eg. adware, bot, spyware, virus), and not on the interactions of users and threat strategies. 
</p>

<p>
<b>What do I have to do?</b> You will be using a custom social platform to interact with others. This will help us to understand the connection between user interactions and spreading of cyber threat on social networks. 
</p>

<p>
<b>What will happen if I take part?</b> If you take part in the experiments, you will be asked to use the aforementioned social platform, in which you and other participants will communicate with each other. You will have to use the platform for 1-2 hour(s). Your voice or appearance will not be recorded, and if any feedback that may be asked to be provided will be in written form. 
</p>

<p>
<b>Do I have to take part?</b> Participation in this research is voluntary, and your refusal or withdrawal will involve no penalty or loss, now or in the future. 
</p>

<p>
<b>What are the possible disadvantages and risks of taking part?</b> Your participation doesn’t involve any disadvantages or discomfort to yourself or other participants. The potential of having any information leaked or being in physical/psychological harm or distress is the same as any experienced in everyday life. 
</p>

<p>
<b>What are the possible benefits of taking part?</b>  Your contribution will allow for the research to have some valuable data to be analysed. This will help in understanding user interactions on social network, and have crucial impact on further study and experiments to be carried out in the future for the project, so please take your participation seriously.
</p>

<p>
<b>What happens if the research study stops earlier than expected?</b> Should the research stop earlier than planned and you are affected in any way we will tell you and explain why. 
</p>

<p>
<b>What type of information will you be collecting from me?</b> Age, gender and your self-reported computer literacy skills. These are required in order to for us to have better understanding if and how socio-demographic features and computer literacy skills affect the spreading of cyber threats. Furthermore, all your interactions with the experimental platform will be recorded.  
</p>

<p>
<b>Will my data be kept confidential?</b> Once published all data will be anonymized, the information about gender and age will be generalized, that is divided into age group and gender for the visualization of the data. You will not be identifiable in any report or publication.
</p>

<p>
<b>Will I be recorded, and how will the record be used in media?</b> No. You will not be recorded.
</p>

<p>
<b>What will happen to the results of the research project?</b> As this is a pilot study, the data will be used for initial analysis, and further improvement of the experiments. We will be able to identify the strengths and weaknesses of the experiment and improve on it in the future. The results of those experiment and the pilot study will be published, and you are free to contact us if you wish to obtain the copy of the results of this research. 
</p>

<p>
<b>Who has ethically reviewed the project?</b> This project has been ethically approved by the University of Greenwich Faculty Research Ethics Committee (FREC) and UREC (University Research Ethics Committee). The committees monitor the application and delivery of the University’s Ethics Review Procedure across the University. 
</p>

<br>

<p>
Thank you for taking part in the research!
</p>
</div>
<form action="#" id="pis_form" align="center">
	<input type="checkbox" name="pis" value="pis" id="pis" required>I can confirm that I have read the Participant Information Sheet
	<br>
	<input type="submit" value="Submit" id="pis_submit">
</form>
<h2 id="q_head" align="center" style="display: none">Questionnaire</h2>
<div id="q_main" align="center" style="display: none;">
	<form action="complete_questionnaire.php">
		<table>
			<tr>
			<td>
			<p>Gender
			<select name="gender">
			    <option disabled selected value> -- select an option -- </option>
				<option value="m">Male</option>
				<option value="f">Female</option>
				<option value="o">Other</option>
			</select>
			</p>
			<p>Age: <input type="number" placeholder="age" name="age" value="" min="18" max="50"></p>
			</td>
			</tr>
			<tr>
			<td>
			<p>Primary Web Browser (e.g. Chrome, Firefox, Safari, Edge) 
				<select id="browser" name="browser" onchange="showTextBox()">
				    <option disabled selected value> -- select an option -- </option>
					<option value="chrome">Chrome</option>
					<option value="safari">Safari</option>
					<option value="firefox">Firefox</option>
					<option value="edge">Edge</option>
					<option value="other">Other (Please specify)</option>
				</select>
			<div id="other_browser" style="display: none;">
				<input type="textbox" name="other_browser" placeholder="Other">
			</div>
			</p>
			<p>Primary OS (e.g. Windows, MacOS, Android, Linux)
				<select name="os">
				    <option disabled selected value> -- select an option -- </option>
					<option value="windows">Windows</option>
					<option value="macos">MacOS</option>
					<option value="android">Android</option>
					<option value="ios">iOS</option>
					<option value="linux">Linux</option>
					<option value="dontknow">I don\'t know</option>
				</select>
			</p>
			
			<p>How often do you use a computer
				<select name="computer_usage">
				    <option disabled selected value> -- select an option -- </option>
					<option value="computer_every_day">Every day</option>
					<option value="computer_several_week">Several times during the week</option>
					<option value="computer_once_week">Once or more per week</option>
					<option value="computer_less_week">One or less per week</option>
				</select>
			</p>
			
			<p>How often do you use social media
				<select name="social_media_usage">
				    <option disabled selected value> -- select an option -- </option>
					<option value="media_every_day">Every day</option>
					<option value="media_several_week">Several times during the week</option>
					<option value="media_once_week">Once or more per week</option>
					<option value="media_less_week">One or less per week</option>
				</select>
			</p>
			</tr>
			</td>
			<tr>
			<td>
			<p><b>1. Do you know how to tell if your computer is hacked or infected?</b></p>

			<div>
			  <input type="radio" id="computer_hacked_yes" name="question1" value="yes" required>
			  <label for="computer_hacked_yes">Yes, I know what to look for to see if my computer is hacked or infected.</label>
			</div>

			<div>
			  <input type="radio" id="computer_hacked_no" name="question1" value="no">
			  <label for="computer_hacked_no">No, I do not know what to look for to see if my computer is hacked or infected.</label>
			</div>
			
			<p><b>2. Is your computer configured to be automatically updated?</b></p>

			<div>
			  <input type="radio" id="updates_yes" name="question2" value="yes" required>
			  <label for="updates_yes">Yes, it is</label>
			</div>

			<div>
			  <input type="radio" id="updates_no" name="question2" value="no">
			  <label for="updates_no">No, it is not</label>
			</div>
			
			<div>
			  <input type="radio" id="updates_idk" name="question2" value="idk">
			  <label for="updates_idk">I do not know.</label>
			</div>
			
			<p><b>3. How careful are you when you open an attachment in email?</b></p>
			
			<div>
			  <input type="radio" id="attachment_ensure" name="question3" value="always_make_sure" required>
			  <label for="attachment_ensure">I always make sure it is from a person I know and I am expecting the email.</label>
			</div>

			<div>
			  <input type="radio" id="attachment_cautious" name="question3" value="as_long_as_i_know_the_person">
			  <label for="attachment_cautious">As long as I know the person or company that sent me the attachment I open it.</label>
			</div>
			
			<div>
			  <input type="radio" id="attachment_always" name="question3" value="nothing_wrong_with_attachments">
			  <label for="attachment_always">There is nothing wrong with opening attachments.</label>
			</div>
			
			<p><b>4. Do you know what a phishing attack is?</b></p>
			
			<div>
			  <input type="radio" id="phishing_yes" name="question4" value="yes" required>
			  <label for="phishing_yes">Yes, I do</label>
			</div>

			<div>
			  <input type="radio" id="phishing_no" name="question4" value="no">
			  <label for="phishing_no">No, I do not.</label>
			</div>
			
			<p><b>5. Do you use the same passwords for your work accounts as you do for your personal accounts at home, such as Facebook, Twitter or your personal email accounts?</b></p>
			
			<div>
			  <input type="radio" id="password_yes" name="question5" value="yes" required>
			  <label for="password_yes">Yes, I do</label>
			</div>

			<div>
			  <input type="radio" id="password_no" name="question5" value="no">
			  <label for="password_no">No, I do not.</label>
			</div>
			</tr>
			</td>
		</table>	
		<br>
		<input type="checkbox" name="ques" value="ques" required>I acknowledge that I have read the Participant Information Sheet and I give my conset to participate in this experiment
		<br>
		<input type="submit">
	</form>
</div>
<script>
function showTextBox(){
	if ($(\'#browser\').val() == \'other\') {
	   $(\'#other_browser\').css({\'display\':\'block\'});
	}
}

document.getElementById("pis_submit").addEventListener("click", function(event){
event.preventDefault()

var pis_checkbox = document.getElementById("pis");
var pis_form = document.getElementById("pis_form");
var pis_doc = document.getElementById("doc");
var pis_header = document.getElementById("header");
var q_head = document.getElementById("q_head");
var q_main = document.getElementById("q_main");

if (pis_checkbox.checked == true){
	pis_form.style.display = "none";
	pis_doc.style.display = "none";
	pis_header.style.display = "none";
	q_head.style.display = "block";
	q_main.style.display = "block";
}
});	

</script>
</body>
</html>
';

?>