<?php

include_once('json_common.php');

/*
 * Echo out the data
 */
$obj = @get_json_data('google', 'activities', '?maxResults=25');

foreach ($obj as $items) {
	if (!is_array($items) || count($items) < 1 ) {
		continue;
	}
	
	$duplicates = array();
	
	foreach($items as $item) {
		
		// Google+ seems to have an abundance of duplicates in their feed (likely historical edits)
		// This is a quick hack to check for duplicates based on title name
		if (array_key_exists($item->title, $duplicates)) {
			continue;
		}
		
		$duplicates[$item->title] = true;
		
		echo '<div class="bs-callout bs-callout-danger"><a target="_blank" href="' . $item->url . '"><h5><strong>';
		echo '<span class="fa fa-google-plus"></span> ' . $item->title . '</strong></h5></a>';
		$message = $item->object->content;
		if (strlen($message) > 500) {
			$message = substr($message, 0, 250) . '...<h4><a target="_blank" href="' . $item->url . '">...</a></h4>';
		}
		echo $message;
		// Format and concat a pretty timestamp
		echo '<p><small>Posted by: <a href="' . $item->actor->url . '">' . $item->actor->displayName . '</a> at ' . 
			date("D, d M Y h:ia ", strtotime($item->published)) . '(GMT ' . sprintf('%+d', date('O')*1/100) . ')</small></p>';
		echo '</div><br>';
	}
}

?>
