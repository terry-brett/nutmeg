<?php
$xml = new DOMDocument();
$xml->formatOutput = true;
$xml->preserveWhiteSpace = false;

function update_tracking($filename, $tag="sent", $message_id, $send_to, $send_from, $time, $u_infected, $m_infected, $f_type){
	global $xml;
	if (file_exists($filename)){
		$xml->load($filename);
		$user_activity = $xml->getElementsByTagName('user_activity')->item(0);
		$action_tag = create_activity_node($tag, $message_id, $send_to, $send_from, $time, $u_infected, $m_infected, $f_type);
		$user_activity->appendChild($action_tag);
		$xml->save($filename);
		
	}
	else {
		$xml = new DOMDocument();
		$root = $xml->createElement('user_activity');
		
		$action_tag = create_activity_node($tag, $message_id, $send_to, $send_from, $time, $u_infected, $m_infected);
		
		$root->appendChild($action_tag);
		$xml->appendChild($root);
		$xml->save($filename);
	}
}

function update_recovery_tracking($filename, $method, $time){
	global $xml;
	
	if (file_exists($filename)){
		$xml->load($filename);
		$user_activity = $xml->getElementsByTagName('user_activity')->item(0);
		$recovery_tag = create_recovery_tag($method, $time);
		$user_activity->appendChild($recovery_tag);
		$xml->save($filename);
	}
	else {
		$xml = new DOMDocument();
		$root = $xml->createElement('user_activity');
		
		$recovery_tag = create_recovery_tag($method, $time);
		
		$root->appendChild($recovery_tag);
		$xml->appendChild($root);
		$xml->save($filename);
	}
	
}

function update_opened_item_tracking($filename, $message_id, $from, $time, $m_infected){
	global $xml;
	
	if (file_exists($filename)){
		$xml->load($filename);
		$user_activity = $xml->getElementsByTagName('user_activity')->item(0);
		$opened_tag = create_opened_item_tag($message_id, $from, $time, $m_infected);
		$user_activity->appendChild($opened_tag);
		$xml->save($filename);
	}
	else {
		$xml = new DOMDocument();
		$root = $xml->createElement('user_activity');
		
		$opened_tag = create_opened_item_tag($message_id, $from, $time, $m_infected);
		
		$root->appendChild($opened_tag);
		$xml->appendChild($root);
		$xml->save($filename);
	}
}

function update_blocked_item_tracking($filename, $tag, $block_id, $total_interactions, $time){
	global $xml;
	
	if (file_exists($filename)){
		$xml->load($filename);
		$user_activity = $xml->getElementsByTagName('user_activity')->item(0);
		$blocked_tag = create_block_node($tag, $block_id, $total_interactions, $time);
		$user_activity->appendChild($blocked_tag);
		$xml->save($filename);
	}
	else {
		$xml = new DOMDocument();
		$root = $xml->createElement('user_activity');
		
		$blocked_tag = create_block_node($tag, $block_id, $total_interactions, $time);
		
		$root->appendChild($blocked_tag);
		$xml->appendChild($root);
		$xml->save($filename);
	}
}

function create_activity_node($tag, $m_id, $send_to, $send_from, $t, $u_infected, $m_infected, $f_type){
	global $xml;
	$action_tag = $xml->createElement($tag);
		
	$message_id_attr = $xml->createAttribute('message_id');
	$message_id_attr->value  = $m_id;
	
	$action_tag->appendChild($message_id_attr);

	if ($tag == "sent" or $tag == "didnt_sent"){
		$to = $xml->createElement('to');
		$to->nodeValue = $send_to;
	}
	else if ($tag = "received"){
		$from = $xml->createElement('from');
		$from->nodeValue = $send_from;
	}		


	$time = $xml->createElement('time');
	$time->nodeValue = $t;

	$user_infected = $xml->createElement('user_infected');
	$user_infected->nodeValue = $u_infected;

	$message_infected = $xml->createElement('message_infected');
	$message_infected->nodeValue = $m_infected;
	
	$file_type = $xml->createElement('file_type');
	$file_type->nodeValue = $f_type;

	//$action_tag->appendChild($message_id);
	if ($to != NUll){
		$action_tag->appendChild($to);
	}else if ($from != NULL){
		$action_tag->appendChild($from);
	}
	
	$action_tag->appendChild($time);
	$action_tag->appendChild($user_infected);
	$action_tag->appendChild($message_infected);
	$action_tag->appendChild($file_type);
	
	return $action_tag;
}

function create_recovery_tag($m, $t){
	global $xml;
	
	$recovery_tag = $xml->createElement('recovered');
	
	$method = $xml->createElement('method');
	$method->nodeValue = $m;
	
	$time = $xml->createElement('time');
	$time->nodeValue = $t;
	
	$recovery_tag->appendChild($method);
	$recovery_tag->appendChild($time);
	
	return $recovery_tag;
}

function create_opened_item_tag($m_id, $f, $t, $m_infected){
	global $xml;
	
	$opened_tag = $xml->createElement('opened_item');
	
	$message_id_attr = $xml->createAttribute('message_id');
	$message_id_attr->value  = $m_id;
	
	$opened_tag->appendChild($message_id_attr);
	
	$from = $xml->createElement('from');
	$from->nodeValue = $f;
	
	$time = $xml->createElement('time');
	$time->nodeValue = $t;
	
	$message_infected = $xml->createElement('message_infected');
	$message_infected->nodeValue = $m_infected;
	
	$user_infected = $xml->createElement('user_infected');
	$user_infected->nodeValue = $m_infected;
	
	$opened_tag->appendChild($from);
	$opened_tag->appendChild($time);
	$opened_tag->appendChild($user_infected);
	$opened_tag->appendChild($message_infected);
	
	return $opened_tag;
}

function create_block_node($tag, $block_id, $ti, $t){
	global $xml;
	$block_tag = $xml->createElement($tag);
	
	if ($tag == "block"){
		$user_id_tag = $xml->createElement("blocked_user");
		$user_id_tag->nodeValue = $block_id;
	}
	else if ($tag == "unblock"){
		$user_id_tag = $xml->createElement("unblocked_user");
		$user_id_tag->nodeValue = $block_id;
	}
	
	$total_interactions = $xml->createElement("total_interactions");
	$total_interactions->nodeValue = $ti;
	
	$time = $xml->createElement("time");
	$time->nodeValue = $t;
	
	$block_tag->appendChild($user_id_tag);
	$block_tag->appendChild($total_interactions);
	$block_tag->appendChild($time);
	
	return $block_tag;
}
?>
