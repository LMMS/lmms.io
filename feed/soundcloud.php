<?php
//include_once('../utils.php');
include_once('json_common.php');

/*
 * Echo out the data
 */

$obj = get_json_data('soundcloud');

if (count($obj) <= 0 || !$obj[0]->user_id) {
	exit;
}

foreach ($obj as $item) {
	$title = $item->title;

	// Cross-linked posts seem to have an empty title.  This changes it to a generic title.
	if (!$title || trim($title) == '') {
		$title = 'LMMS Announcement';
	}

	echo '<div class="bs-callout bs-callout-warning" style="overflow: auto;" ><a target="_blank" href="' . $item->permalink_url . '"><h5><strong>';
	echo '<span class="fa fa-soundcloud"></span> ' . $item->title . '</strong></h5>';
	echo '<img class="img-thumbnail" style="float:left; margin: 5px; width: 60px; height: 60px;" src="' . ($item->artwork_url ? $item->artwork_url : $item->user->avatar_url) . '"/>';
	echo '</a>';
	echo '<p>' . $item->description . '</p>';
	// Format and concat a pretty timestamp
	echo '<p><small>Created by: <a href="' . $item->user->permalink_url . '">' . $item->user->username . '</a> at ' .
		date("D, d M Y h:ia ", strtotime($item->created_at)) . '(GMT ' . sprintf('%+d', date('O')*1/100) . ')</small></p>';
	echo '</div><br>';
}

?>
