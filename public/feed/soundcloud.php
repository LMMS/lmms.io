<?php
include_once('feed/json_common.php');

/*
 * Echo out the data
 */

$obj = get_json_data('soundcloud');

if (count($obj) <= 0 || !$obj[0]->user_id) {
	exit;
}

echo '<table class="table table-striped"><th><h2 class="text-center">LMMS SoundCloud</h2></th>';
foreach ($obj as $item) {
	create_row(
		'soundcloud', 					// $service	i.e. "facebook"
		$item->title, 						// $title 	i.e. "LMMS Released!"
		"javascript:embedSound('#div-" . $item->id . "','" . $item->id . "')", // $href	i.e. "http://facebook.com/post1234"
		trim_feed($item->description, $item->permalink_url),	// $message   i.e "We are pleased to announce..."
		$item->user->username, 			// $author	i.e. "John Smith"
		$item->user->permalink_url, 	// $author_href	i.e. "http://facebook.com/user1234"
		$item->created_at,				// $date	i.e. "2014-01-01 00:00:00"
		($item->artwork_url ? $item->artwork_url : $item->user->avatar_url), // $thumbnail	i.e. "http://facebook.com/etc.png"
		'div-' . $item->id
	);
}
echo '</table>';

?>
