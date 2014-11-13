<?php

require_once('../lib/RemWiki/RemWiki.php');

function documentationPage($page=null)
{
	global $app;
	$wiki = new RemWiki\RemWiki('http://lmms.sourceforge.net/wiki/');

	if ($page === null or $page === '') {
		$page = 'Main_Page';
	}

	$json = $wiki->parse($page);

	return $app['twig']->render('documentation.twig', [
		'json' => $json,
		'text' => $json->text->{'*'}
	]);
}
