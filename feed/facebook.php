<?php
include_once('../utils.php');
include_once('json_common.php');
require_once('../vendor/autoload.php');

/*
 * Echo out the data
 */

$obj = get_json_data('facebook');

if (count($obj) <= 0 || !$obj->entries) {
		exit;
}

echo '<table class="table table-striped"><th><h2 class="center">LMMS Facebook</h2></th>';
foreach ($obj->entries as $item) {
		$title = $item->title;

		// Cross-linked posts seem to have an empty title.  This changes it to a generic title.
		if (!$title || trim($title) == '') {
			$title = 'LMMS Announcement';
		}

		echo '<tr><td><a target="_blank" href="' . $item->alternate . '"><h3><strong>';
		echo '<span class="fa fa-facebook-square"></span> ' . $title . '</strong></h3></a>';
		$message = cleanse_urls($item->content, $item->alternate);

		echo $message;
		// Format and concat a pretty timestamp
		echo '<p><small>Posted by: <a href="' . 'http://facebook.com/' . get_json_id('facebook') . '">' . $item->author->name . '</a> at ' .
			date("D, d M Y h:ia ", strtotime($item->published)) . '(GMT ' . sprintf('%+d', date('O')*1/100) . ')</small></p>';
		echo '</td></tr>';
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
		if (!$element->find('img') && is_image($element)) {
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

/*
 * Checks an href URL for most well-known image formats
 */
function is_image($a_tag) {
	$image_exts = explode(',', 'png,jpg,jpeg,gif,bmp,tif,tiff,svg');
	foreach($image_exts as $ext) {
		if (str_endswith($a_tag->href, '.' . trim($ext), true)) {
			return true;
		}
	}
	return false;
}

?>
<script>
// Facebook JavaScript stub to prevent lingering onmouseover and onclick page errors
var LinkshimAsyncLink = { swap:function(caller, url) {} };
</script>
