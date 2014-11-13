<?php

include_once('feed/json_common.php');

/*
 * Echo out the data
 */

$obj = get_json_data('google', 'activities', '?maxResults=25');

echo '<table class="table table-striped"><th><h2 class="text-center">LMMS Google+</h2></th>';

// Sort on publish date
usort($obj->items, function($a,$b) {
	return (strtotime($a->published) > strtotime($b->published) ? -1 : 1);
});

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

		create_row(
			'google+', 					// $service	i.e. "facebook"
			$item->title, 				// $title 	i.e. "LMMS Released!"
			$item->url, 				// $href	i.e. "http://facebook.com/post1234"
			trim_feed($item->object->content, $item->url),	// $message   i.e "We are pleased to announce..."
			$item->actor->displayName, 	// $author	i.e. "John Smith"
			$item->actor->url, 			// $author_href	i.e. "http://facebook.com/user1234"
			$item->published			// $date	i.e. "2014-01-01 00:00:00"
		);
	}
}
echo '</table>';

?>
