<?php
include_once('force-https.php');
include_once('utils.php');
include_once('feed/json_common.php');
include_once('../vendor/kellan/magpierss/rss_fetch.inc');
require_once('../vendor/autoload.php');

if ( !defined('MAGPIE_OUTPUT_ENCODING') ) {
	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
}

$rss = fetch_rss(get_protocol() . 'lmms.io/forum/feed.php');

echo '<table class="table table-striped"><th><h2 class="text-center">LMMS Forums</h2></th>';
foreach ($rss->items as $item) {
	// Prepare the html a bit
	$atom = $item['atom_content'];
	$atom = str_replace('<br />', ' ', $atom);
	$atom = str_replace('<hr />', '', $atom);
	$atom = str_replace('\n', '', $atom);
	$atom = str_replace('<p>Statistics:', '<p class="dummy">', $atom);
	$atom = str_replace('</code>', '</pre>', str_replace('<code>', '<pre>', $atom));



	create_row(
		'forums', 				// $service	i.e. "facebook"
		$item['title'], 		// $title 	i.e. "LMMS Released!"
		$item['id'], 			// $href	i.e. "https://facebook.com/post1234"
		cleanse_html($atom, $item['id'])	// $message   i.e "We are pleased to announce..."
		//$author, 				// $author	i.e. "John Smith"
		//$author_href, 		// $author_href	i.e. "https://facebook.com/user1234"
		//$date					// $date	i.e. "2014-01-01 00:00:00"
	);
}
echo '</table>';

/*
 * Cleanses HTML content for better display as a feed on the website
 */
function cleanse_html($atom, $href) {
	$html = SimpleHtmlDom\str_get_html($atom);
	foreach($html->find('img') as $element) {
		// Skip smilies
		if (str_contains($element->src, '/smilies/')) {
			continue;
		}

		$class = 'img img-thumbnail forum-thumb';
		$element->outertext = '<a href="' . $href . '"><img class="' . $class . '" src="' . scale_image($element->src, 200) . '"></a>';
	}

	foreach($html->find('p') as $element) {
		if ($element->class == 'dummy') {
			$element->outertext = '<small class="feed-small">' . $element->innertext . '</small>';
		}
	}

	$html->save;
	return $html;
}

?>
