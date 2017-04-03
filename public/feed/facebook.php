<?php
include_once('utils.php');
include_once('feed/json_common.php');
require_once('../vendor/autoload.php');

/*
 * Echo out the data
 */

$obj = get_json_data('facebook');

if (count($obj) <= 0 || !$obj->entries) {
		exit;
}

echo '<table class="table table-striped"><th><h2 class="text-center">LMMS Facebook</h2></th>';
foreach ($obj->entries as $item) {
	create_row(
		'facebook', 							// $service	i.e. "facebook"
		$item->title, 							// $title 	i.e. "LMMS Released!"
		$item->alternate, 						// $href	i.e. "http://facebook.com/post1234"
		cleanse_urls($item->content, $item->alternate),		// $message   i.e "We are pleased to announce..."
		$item->author->name, 					// $author	i.e. "John Smith"
		'http://facebook.com/' . get_json_id('facebook'), 	// $author_href	i.e. "http://facebook.com/user1234"
		$item->published						// $date	i.e. "2014-01-01 00:00:00"
	);
}
echo '</table>';

/*
 * Fixes facebook JSON feed nuances such as:
 * 	- Relative linking (i.e. href="/makefreemusic")
 *  - URL redirecting (i.e. ...l.php?u=http://my_real_link)
 *  - Render cross-posted thumbnails (i.e. <a src="https://fbcdn-profile-a.akamaihd.net...>)
 */
function cleanse_urls($str, $article_url) {
	$html = SimpleHtmlDom\str_get_html($str);
	foreach($html->find('a') as $element) {
		// Fix cross posts, especially those from LMMSChallenge facebook
		if (!$element->find('img') && is_feed_image($element)) {
			$element->innertext = '<img class="img-thumbnail fb-thumb" src="' . $element->href . '"/>';
			$element->href = $article_url;
		}
		// Fix unnecessary facebook redirects
		$pos = strpos(strtolower($element->href), '.php?u=http%3a%2f%2f') + strpos(strtolower($element->href), '.php?u=https%3a%2f%2f');
		if ($pos) {
			// Remove js callbacks
			$element->onmouseover = '';
			$element->onclick = '';
			// Strip out the facebook params
			$href = explode('&', $element->href)[0];
			// Isolate and decode the proper url
			$element->href = urldecode(substr($href, $pos+7));
		}
		// Fix relative facebook URLs
		if (str_startswith($element->href, '/')) {
			$element->href = 'https://www.facebook.com' . $element->href;
		}

	}

	foreach ($html->find('img') as $img) {
		$img->class = ($img->class ? $img->class . ' ' : '') . 'img-thumbnail fb-thumb';
	}

	return $html->save();
}

?>
<script>
// Facebook JavaScript stub to prevent lingering onmouseover and onclick page errors
var LinkshimAsyncLink = { swap:function(caller, url) {} };
</script>
