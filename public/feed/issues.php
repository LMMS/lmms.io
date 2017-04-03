<?php

include_once('feed/json_common.php');

/*
 * Maximum number of displayed items
 */
$max=20;

/*
 * Creates an array of relational JSON objects from cached or online GitHub data
 */
$obj = get_json_data('github', 'issues', '?state=open');

/*
 * Loop through items and echo
 */
$count = 0;

/*
 * Echo our data to the page
 */
echo '<table class="table table-striped"><th><h2 class="text-center">LMMS GitHub</h2></th>';
foreach($obj as $item) {
	$title = 'GitHub #' . $item->number . ' &bull; ' . $item->title;
	create_row(
		'github', 					// $service	i.e. "facebook"
		$title, 					// $title 	i.e. "LMMS Released!"
		$item->html_url, 			// $href	i.e. "http://facebook.com/post1234"
		trim_feed($item->body, $item->html_url),	// $message   i.e "We are pleased to announce..."
		$item->user->login, 		// $author	i.e. "John Smith"
		$item->user->html_url, 		// $author_href	i.e. "http://facebook.com/user1234"
		$item->created_at			// $date	i.e. "2014-01-01 00:00:00"
	);


	if ($count++ == $max) {
		break;
	}
}
echo '</table>';

?>
