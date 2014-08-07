<?php

include_once('json_common.php');

/*
 * Echo out the data
 */

$obj = get_json_data('facebook');

if (count($obj) <= 0 || !$obj->entries) {
		exit;
}

foreach ($obj->entries as $item) {
		$title = $item->title;
		if (!$title || trim($title) == '') {
			$title = 'No Subject';
		}
		
		echo '<div class="bs-callout bs-callout-info"><a target="_blank" href="' . $item->alternate . '"><h5><strong>';
		echo '<span class="fa fa-facebook-square"></span> ' . $title . '</strong></h5></a>';
		$message = $item->content;
		$message = str_replace('href="/LMMSchallenge/', 'href="http://www.facebook.com/LMMSchallenge/', $message);

		echo $message;
		// Format and concat a pretty timestamp
		echo '<p><small>Posted by: <a href="' . 'http://facebook.com/' . get_json_id('facebook') . '">' . $item->author->name . '</a> at ' . 
			date("D, d M Y h:ia ", strtotime($item->published)) . '(GMT ' . sprintf('%+d', date('O')*1/100) . ')</small></p>';
		echo '</div><br>';
}

?>
