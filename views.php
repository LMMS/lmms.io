<?php

require_once('../lib/RemWiki/RemWiki.php');

/* Documentation page */
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

/* Downloads page */
require_once('feed/releases.php');
$app['twig']->addFunction(new Twig_SimpleFunction('get_releases', 'get_releases', ['is_safe' => ['html']]));
function downloadPage()
{
	global $app;
	return $app['twig']->render('download/index.twig');
}

require_once('artwork.php');
$app['twig']->addFunction(new Twig_SimpleFunction('create_artwork_item', 'create_artwork_item', ['is_safe' => ['html']]));
function artworkPage()
{
	global $app;
	return $app['twig']->render('download/artwork.twig');
}

/* Screenshots page */
function screenshotsPage()
{
	global $app;

	$screenshots = [];

	$ss_dir = 'img/screenshots/';
	$files = scandir($ss_dir);
	foreach($files as $file) {
		if (str_startswith($file, 'ss_', true) && str_endswith($file, '.png', true)) {
			$screenshots[] = [
				'title' => humanize_title($file),
				'path' => '/' . $ss_dir . $file
			];
		}
	}

	return $app['twig']->render('screenshots.twig', [
		'screenshots' => $screenshots
	]);
}

/*
 * Creates an english-readable title from a file name
 */
function humanize_title($filename) {
	$replacement = array(
		'ss' => '',
		'bb' => 'B&B Editor',
		'mixer' => 'FX Mixer',
		'roll' => 'Roll Editor',
		'plugins' => 'Native Instruments',
		'automation' => 'Automation Editor',
		'vst' => 'VSTi Running via Vestige'
	);

	$title_split = explode('_', $filename);

	$found = false;
	foreach($title_split as &$item) {
		// Skip 01, 02, etc
		if (is_numeric($item)) {
			$item = '';
			continue;
		}
		// Substitute array reference with the text above
		if (str_contains($item, '.png', false)) {
			$item = str_replace('.png', '', $item);
		}

		if (array_key_exists($item, $replacement)) {
			$temp = $replacement[$item];
			$item = ($found ? ', ' : ' ') . $temp;
			$found = trim($temp) != '' ? true : false;
		} else {
			$item = ' ' . ucfirst($item);
		}
	}

	return trim(implode('', $title_split));
}
