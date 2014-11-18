<?php

include('../header.php');
require_once('../../lib/RemWiki/RemWiki.php');

if (array_key_exists('page', $_GET)) {
	$page = $_GET['page'];
} else {
	$page = 'Main_Page';
}

$wiki = new RemWiki\RemWiki('http://lmms.io/wiki/');
$json = $wiki->parse($page);

echo '<h1>' . $json->displaytitle . '</h1>';
echo $json->text->{'*'};

include('../footer.php');
