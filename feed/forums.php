<head>
<meta charset="UTF-8">
</head>
<?php

include('rss/rss_fetch.inc');

if ( !defined('MAGPIE_OUTPUT_ENCODING') ) {
	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
}

$rss = fetch_rss('http://lmms.tuxfamily.org/forum/feed.php');

foreach ($rss->items as $item) {
	echo '<a class="label label-info" target="new" href="' . $item['id'] . '">' . $item['title'] . '</a>';
	echo '<p>' . $item['atom_content'] .  '</p><br>';
}

?>
