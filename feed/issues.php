<?php

include('github_common.php');

/*
 * Maximum number of displayed items
 */
$max=20;

/*
 * Creates an array of relational JSON objects from cached or online GitHub data
 */
$obj = @get_github_data('issues', '?state=open');

/*
 * Loop through items and echo
 */
$count = 0;

/*
 * Echo our data to the page
 */
foreach($obj as $item) {
	echo '<div class="bs-callout bs-callout-info"><h5><strong><a target="_blank" href="' .
		$item->html_url . '"><span class="fa fa-github"></span> GitHub #' . $item->number . ' &bull; ';
	echo $item->title. '</a></strong></h5>';
	$message = $item->body;
	if (strlen($message) > 500) {
		$message = substr($message, 0, 250) . '...<h4><a target="_blank" href="' . $item->html_url . '">...</a></h4>';
	}
	echo $message;
	// Format and concat a pretty timestamp
	echo '<small>Posted by: <a href="' . $item->user->html_url . '">' . $item->user->login . '</a> at ' . 
		date("D, d M Y h:ia ", strtotime($item->created_at)) . '(GMT ' . sprintf('%+d', date('O')*1/100) . ')</small>';
	echo '</div>';
	if ($count++ == $max) {
		break;
	}
}

?>
