<?php
include_once('../utils.php');
include_once('json_common.php');
include('../vendor/kellan/magpierss/rss_fetch.inc');

if ( !defined('MAGPIE_OUTPUT_ENCODING') ) {
	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
}

$rss = fetch_rss('http://lmms.tuxfamily.org/forum/feed.php');

echo '<table class="table table-striped"><th><h2 class="center">LMMS Forums</h2></th>';
foreach ($rss->items as $item) {
	// Prepare the html a bit
	$atom = $item['atom_content'];
	$atom = str_replace('<br />', ' ', $atom);
	$atom = str_replace('<hr />', '', $atom);
	$atom = str_replace('\n', '', $atom);
	$atom = str_replace('<p>Statistics:', '<p class="forum-stats">', $atom);

	create_row(
		'forums', 				// $service	i.e. "facebook"
		 $item['title'], 		// $title 	i.e. "LMMS Released!"
		$item['id'], 			// $href	i.e. "http://facebook.com/post1234"
		$atom					// $message   i.e "We are pleased to announce..." 
		//$author, 				// $author	i.e. "John Smith"
		//$author_href, 		// $author_href	i.e. "http://facebook.com/user1234"
		//$date					// $date	i.e. "2014-01-01 00:00:00"
	);
}
echo '</table>';

?>
