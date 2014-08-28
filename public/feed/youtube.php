<?php

include_once('json_common.php');

/*
 * Echo out the data
 */

$videos_url = 'https://www.youtube.com/user/LMMSOfficial/videos';
$obj = get_json_data('youtube', 'activities', '&part=snippet&maxResults=25');

echo '<table class="table table-striped"><th><h2 class="center">LMMS YouTube</h2></th>';
foreach ($obj as $items) {
	if (!is_array($items) || count($items) < 1 ) {
		continue;
	}
	
	$duplicates = array();

	foreach($items as $item) {
		$item = $item->snippet;
		
		// YouTube seems to have some duplicates in their feed (likely historical edits)
		// This is a quick hack to check for duplicates based on title name
		if (array_key_exists($item->title, $duplicates)) {
			continue;
		}

		$duplicates[$item->title] = true;


		$id = parse_youtube_id($item->thumbnails->default->url);
		$url = $id ? 'http://www.youtube.com/watch?v=' . $id : '';
		
		create_row(
			'youtube', 					// $service	i.e. "facebook"
			$item->title, 		// $title 	i.e. "LMMS Released!"
			"javascript:embedVideo('#div-" . $id . "','" . $id . "')", 						// $href	i.e. "http://facebook.com/post1234"
			trim_feed($item->description, $url),	// $message   i.e "We are pleased to announce..." 
			$item->channelTitle, 		// $author	i.e. "John Smith"
			$videos_url, 				// $author_href	i.e. "http://facebook.com/user1234"
			$item->publishedAt,			// $date	i.e. "2014-01-01 00:00:00"
			$item->thumbnails->default->url,
			'div-' . $id
		);
	}
}
echo '</table>';

// The feed doesn't give us a clean URL to the video
// So we parse it from the thumbnail image URL
function parse_youtube_id($thumbnail) {
	$arr = explode('/', $thumbnail);
	$i = count($arr) - 2;
	return ($i > 0 ? $arr[$i] : '');
}

?>
