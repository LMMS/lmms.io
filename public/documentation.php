<?php

include('header.php');
require_once('../lib/RemWiki/RemWiki.php');

$uri = explode('/', $_SERVER['REQUEST_URI']);

$page = (implode('/', array_slice($uri, 2)));

if ($page === '') {
	$page = 'Main_Page';
}

$wiki = new RemWiki\RemWiki('http://lmms.sourceforge.net/wiki/');
$json = $wiki->parse($page);

echo '<h1>' . $json->displaytitle . '</h1>';
echo $json->text->{'*'};

include('footer.php');
