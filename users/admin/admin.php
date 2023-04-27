<?php

//create_view();

echo '<!DOCTYPE html>
	<html lang="en">
	<head>
	  <meta charset="utf-8">
	  <script src="https://d3js.org/d3.v3.js"></script>
	  <script src="../script/jsnetworkx.js"></script>
	  <title>Admin</title>
	  
	  <style>
		html, body {
			height:100%;
		}
		.center {
			margin: auto;
			//border: 2px solid #8c939e;
			padding: 10px;
			height: 95%;
		}
		.inner {
			margin: auto;
			//border: 1px solid #b3b6bc;
			margin-top: 10px;
			padding: 20px;
			width: 80%;
		}
		#container {
			width: 100%;
			height: 100%;
		}
		#left {
			float: left;
			width: 31%;
			height: 100%;
		}
		#right {
			float: right;
			width: 31%;
			height: 100%;
		}
		#center {
			margin: 0 auto;
			width: 40%;
			height: 100%;
		}
		textarea { 
			border-style: none; 
			border-color: Transparent; 
			overflow: auto;  
			resize: none;			
		}
		table, th, td {
		  border: 1px solid black;
		  border-collapse: collapse;
		  border-color: #d3d3d3;
		}
		.ui-slider-range {
		   background:green;
		}
		.percent {
		  color:green;
		  font-weight:bold;
		  text-align:center;
		  width:100%;
		  border:none;
		  background: #37474F;
		}
	  </style>	  
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
		<link rel="stylesheet" href="../styles/css/main.css">
		<link rel="stylesheet" href="../styles/css/admin_menu.css">
	</head>
	<body>
	  <div id="page-wrapper">
		
	<div class="top_bar">	
	<h1 style="position: fixed; top: -15px; left: 20px; color:#fff;">Admin</h1>	
	  <div class="navigation-container space-evenly">
		  <p class="nav">Select round:</p>
		  <form action="" method="post">
		  <select name="rounds" id="rounds">
			<!-- <option title="(Intro)" value="r1">Round 1(Intro)</option> -->
			<option title="No infection, basic UI. Users are informed who gift came from after opening the item" value="r1">Round 1 </option>
			<option title="Infection exists, no information about it, users know who the messages are from" value="r2">Round 2 </option>
			<!-- <option title="Infection exists, users will know who it\'s from at the end of the round, send to a known friend" value="r4">Round 4</option> -->
			<!-- <option title="Users are informed who gift came from after opening the box" value="r3">Round 3</option> -->
			<option title="Users can see who the gift came from before opening item. Choose who to send to." value="r3">Round 3</option>
			<option title="Enable antivirus scan, block users" value="r4">Round 4</option>
		  </select>
		  <input type="submit">
		</form>

	  
		  
		  <div id="timer-container"></div>
		  <p class="nav">Round: 1</p>
	  </div>
	</div>
	  <div class="top-background"></div>

		<div id="status" style="display: none;">Connecting...</div>

		<ul id="messages"></ul>

		<form id="message-form" action="#" method="post">
		  <textarea style="display: none" id="message" placeholder="Write your message here..." required></textarea>			
		  <button style="display: none" id="submissive_button" ="submit">Send Message</button>
		  <button style="display: none" type="button" id="close">Close Connection</button>
		</form>
	  </div>
	  	  <nav role="navigation">
		  <div id="menuToggle" class="menuToggle">
			
			<input type="checkbox" id="menu_checkbox"/>
			
			<span class="burger_menu"></span>
			<span class="burger_menu"></span>
			<span class="burger_menu"></span>
			
			<ul id="menu">
				<form action="" method="post" id="model_form">
				<select name="select_model" id="select_model" onchange="select_list()">
				  <option value="null">[Choose Option Below]</option>
				  <option value="ba">Barabasi-Albert</option>
				  <option value="ws">Watts-Strogatz</option>
				  <option value="er">Erdős–Rényi</option>
				  <option value="cg">Complete graph</option>
				</select>
				<input type="submit" name="button" value="Submit"/><br>
				<input type="text" name="n_val" id="n_val" style="display: none" placeholder="N"><br>
				<input type="text" name="p_val" id="p_val" style="display: none" placeholder="p"><br>
				<input type="text" name="m_val" id="m_val" style="display: none" placeholder="m"><br>
				
				<div class="container">
				  <div class="row" style="width: 70%; margin-left: -30px">
					<div class="project col-md-4">
					  <h2 class="text-center">
						<input type="text" class="percent" name="fraction_of_infected" readonly />
					  </h2>
					  <h3 class="text-center">infected</h3>
					  <div class="bar"></div>
					</div>    
				  </div>
				</div>
			</form>
			</ul>
		  </div>
		</nav>

	  
	</div>

	
	
	<div id="canvas" style="height: 40%;"></div>
	';
	
	$filename = '../start';

	if (!file_exists($filename)) {
		echo '<div class="start_form">
		<form id="start-game" action="#" method="post">		
		  <button id="start_game" style="color: black;" type="submit">Start game</button>
		</form>
		</div>';
	}	
	else {
		echo "<script>
		reloadTimer(); 
	
		var timeout = setInterval(reloadTimer, 1000); // update after 1s
		function reloadTimer(){
			$('#timer-container').load('../script/admin_timer.php');
		}
		</script>";
	}
	
	echo '
	  <script>
		function myFunction(chk_id, element_id) {
		  var checkBox = document.getElementById(chk_id);
		  var messageField = document.getElementById(\'message\');
		  var form = document.getElementById("message-form");
		  var submit_btn = document.getElementById("submissive_button");
		  if (checkBox.checked == true){
			messageField.value = element_id + ",true";
			submissive_button.click();
			//form.submit();
		  } else {
			 messageField.value = element_id + ",false";
			 //form.submit();
			 submissive_button.click();
		  }
		}
		function select_list(){
			var model = document.getElementById("select_model").value;
			
			if (model =="ba"){
				document.getElementById("n_val").style.display = "none";
				document.getElementById("m_val").style.display = "block";
				document.getElementById("p_val").style.display = "none";
			}
			else if (model =="er"){
				document.getElementById("n_val").style.display = "none";
				document.getElementById("p_val").style.display = "block";
				document.getElementById("m_val").style.display = "none";
			}
			else if (model =="ws"){
				document.getElementById("n_val").style.display = "none";
				document.getElementById("p_val").style.display = "block";
				document.getElementById("m_val").style.display = "block";
				}
			else if (model =="cg"){
				document.getElementById("n_val").style.display = "none";
				document.getElementById("p_val").style.display = "none";
				document.getElementById("m_val").style.display = "none";
			}
		}
	</script>
	<script src=\'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js\'></script>
	<script src=\'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js\'></script>
	<script src="../script/app.js"></script>
	<script>
	$(function() {
	  $(\'.project\').each(function() {
		var $projectBar = $(this).find(\'.bar\');
		var $projectPercent = $(this).find(\'.percent\');
		var $projectRange = $(this).find(\'.ui-slider-range\');
		$projectPercent.val("10%");
		$projectBar.slider({
		  range: "min",
		  animate: true,
		  value: 10,
		  min: 0,
		  max: 100,
		  step: 1,
		  slide: function(event, ui) {
			$projectPercent.val(ui.value + "%");
		  },
		  change: function(event, ui) {
			var $projectRange = $(this).find(\'.ui-slider-range\');
			var percent = ui.value;
			if (percent < 30) {
			  $projectPercent.css({
				\'color\': \'green\'
			  });
			  $projectRange.css({
				\'background\': \'green\'
			  });
			} else if (percent > 31 && percent < 70) {
			  $projectPercent.css({
				\'color\': \'gold\'
			  });
			  $projectRange.css({
				\'background\': \'gold\'
			  });
			} else if (percent > 70) {
			  $projectPercent.css({
				\'color\': \'red\'
			  });
			  $projectRange.css({
				\'background\': \'red\'
			  });
			}
		  }
		});
	  })
	})	
	</script>
	<script type=\'text/javascript\'>
	$(\'#start_game\').click(function(){		
	 $.ajax({
	 type: "POST",
	 url: "start_game.php",
	 data: "model=' . $select_model . '&round=' . $round_number . '",
	 success: function(msg){
     alert("Game started");
	 },
	 error: function(XMLHttpRequest, textStatus, errorThrown) {
		alert("Error starting game");
	 }
	 });
	});
	</script>
	<h1>enable the function in load_graph.php for si model</h1>
	</body>
	</html>';
$select_model=$_POST['select_model'];
$n_val = $_POST['n_val'];
$m_val = $_POST['m_val'];
$p_val = $_POST['p_val'];
$fraction_of_infected = substr( $_POST['fraction_of_infected'], 0, -1)/100;

if ($select_model == "ba"){
	if ($m_val != ""){	
		$g = shell_exec("python3 sql_network_generator.py Barabasi-Albert " . $m_val . " " . $fraction_of_infected);
		echo "python3 sql_network_generator.py Barabasi-Albert " . $m_val . " " . $fraction_of_infected;
		echo("<meta http-equiv='refresh' content='1'>");
	}
}
else if ($select_model == "er"){
	if ($p_val != ""){
		$g = shell_exec("python3 sql_network_generator.py ER " . $p_val  . " " . $fraction_of_infected);
		echo "python3 sql_network_generator.py ER " . $p_val  . " " . $fraction_of_infected;
		echo("<meta http-equiv='refresh' content='1'>");
	}
}
else if ($select_model =="ws"){
	if ($p_val != "" && $m_val != ""){
		$g = shell_exec("python3 sql_network_generator.py Watts-Strogatz " . $m_val . " " . $p_val  . " " . $fraction_of_infected);
		echo "python3 sql_network_generator.py Watts-Strogatz " . $m_val . " " . $p_val  . " " . $fraction_of_infected;
		echo("<meta http-equiv='refresh' content='1'>");
	}
}
else if ($select_model =="cg"){
	$g = shell_exec("python3 sql_network_generator.py Complete_graph "  . $fraction_of_infected);
	echo "python3 sql_network_generator.py Complete_graph "  . $fraction_of_infected;
	echo("<meta http-equiv='refresh' content='1'>");
}

$select_round=$_POST["rounds"];
$round_number = substr($select_round, -1);

if ($round_number >= 1){
	copy('../rounds/round' . $round_number . '/index.php', '../index.php');
	
	$round_file = "round.txt";
	file_put_contents($round_file, $round_number);	
}

$graph_data = explode("_", $g);
$graph = $graph_data[0];
$color = $graph_data[1];

$_SESSION["round"] = $round_number;
$_SESSION["model"] = $select_model;

echo '

<script>
var G = new jsnx.Graph(' . $graph . ');
' . $color . '
jsnx.draw(G, {
	element: \'#canvas\',  
	withLabels: true, 
	edgeStyle: {
		\'stroke-width\': 5
	},
	nodeStyle: {
		fill: function(d) { 
			 return d.data.color || \'0f0\';
		}
	},
	edgeStyle: {
			fill: \'#999\'
	},	
	labelStyle: {fill: \'white\'},
	stickyDrag: true	
}, true);


</script>
<script>  
	// call when page loads
	reloadGraph(); 
  
	var timeout = setInterval(reloadGraph, 1000); // update after 1.0s
	function reloadGraph () {
     $(\'#graph\').load(\'load_graph.php?graph=' . base64_encode($graph) . '\');
	}
 </script>
 <div id="graph"></div>
 <script>
	var i = 1;
    setInterval(function () {
		update();
      i++;
    }, 700);
 </script>';
 
 
function save_setup(){
	global $select_model, $round_number;
	fwrite($fp, $outputstring);
	$filename = "../data/round_setup/$round_number.xml";
	$fp = fopen("".$filename, 'wb');

	$outputstring = "<round_info>
		<level>$round_number</level>
		<setup>
			<graph>" . strtoupper($select_model) . "</graph>
			<constants>
			" . get_constants() . "
			</constants>
		</setup>
	</round_info>";
 }
 
function get_constants(){	
	$contants = fopen("../constants.xml","r");

	$content = "";

	while(! feof($contants))
	  {
		$content .= fgets($contants);
	  }

	fclose($contants);
	
	return $content;
}
?>
