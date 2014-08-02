<?php

include('rss/rss_fetch.inc');

if ( !defined('MAGPIE_OUTPUT_ENCODING') ) {
	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
}

$rss = fetch_rss('http://lmms.tuxfamily.org/forum/feed.php');

foreach ($rss->items as $item) {
	// Prepare the html a bit
	$atom = $item['atom_content'];
	$atom = str_replace('<br />', ' ', $atom);
	$atom = str_replace('<hr />', '', $atom);
	$atom = str_replace('\n', '', $atom);
	$atom = str_replace('<p>Statistics:', '<p style="font-size:75%;">', $atom);
	echo '<div class="bs-callout bs-callout-success"><a target="_blank" href="' . $item['id'] . '"><h5><strong><span class="fa fa-comments"></span> ' . $item['title'] . '</strong></h5></a>';
	echo  $atom .  '</div><br>';
}

/*
 * Helper function to replace first occurance
 */
function str_replace_first($find, $replace, $subject) {
	return implode($replace, explode($find, $subject, 2));
}

?>
