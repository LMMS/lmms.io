<?php

include_once('json_common.php');

/*
 * Echo out the data
 */
 
// FIXME - Playlists probably aren't the best thing to feed to our page.
// Consider uploads instead (once we have some).
$playlist_id = 'PLQ3DxyvaP9n270bhIYnif5jnTlZSdml3G';
$obj = get_json_data('youtube', 'playlistItems', '&playlistId=' . $playlist_id . '&part=snippet&maxResults=25');

echo '<table class="table table-striped"><th><h2 class="center">LMMS YouTube</h2></th>';
foreach ($obj as $items) {
	if (!is_array($items) || count($items) < 1 ) {
		continue;
	}

	foreach($items as $item) {
		$item = $item->snippet;
		$url = parse_youtube_url($item->thumbnails->default->url);
		create_row(
			'youtube', 					// $service	i.e. "facebook"
			$item->title, 		// $title 	i.e. "LMMS Released!"
			$url, 						// $href	i.e. "http://facebook.com/post1234"
			trim_feed($item->description, $url),	// $message   i.e "We are pleased to announce..." 
			$item->channelTitle, 		// $author	i.e. "John Smith"
			'https://www.youtube.com/playlist?list=' . $playlist_id, 			// $author_href	i.e. "http://facebook.com/user1234"
			$item->publishedAt,			// $date	i.e. "2014-01-01 00:00:00"
			$item->thumbnails->default->url
		);
	}
}
echo '</table>';

function parse_youtube_url($thumbnail) {
	$arr = explode('/', $thumbnail);
	$i = count($arr) - 2;
	return ($i > 0 ? 'http://www.youtube.com/watch?v=' . $arr[$i] : '');
}

?>
