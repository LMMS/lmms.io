<?php
require_once('../vendor/autoload.php');
require_once('../lib/RemWiki/RemWiki.php');
require_once('../lib/Releases.php');

/* Documentation page */
function documentationPage($page=null)
{
	global $app;
	$wiki = new RemWiki\RemWiki(get_protocol() . 'lmms.io/wiki/');

	if ($page === null or $page === '') {
		$page = 'Main_Page';
	}

	$json = $wiki->parse($page);

	return $app['twig']->render('documentation.twig', [
		'json' => $json,
		'text' => $json['text']['*']
	]);
}

/* Downloads page */
function downloadPage()
{
	global $app;

	try {
		$releases = new Releases();

		$winstable = [$releases->latestWin32Asset(), $releases->latestWin64Asset()];
		$winpre = [$releases->latestWin32Asset(false), $releases->latestWin64Asset(false)];
		$osxstable = $releases->latestOSXAssets();
		$osxpre = $releases->latestOSXAssets(false);
		$linstable = $releases->latestLinuxAssets();
		$linpre = $releases->latestLinuxAssets(false);

		if ($winstable && $winpre && ($winpre[0]['created_at'] < $winstable[0]['created_at']))
			$winpre = null;
		if ($osxstable && $osxpre && ($osxpre[0]['created_at'] < $osxstable[0]['created_at']))
			$osxpre = null;
		if ($linstable && $linpre && ($linpre[0]['created_at'] < $linstable[0]['created_at']))
			$linpre = null;

		$vars = [
			'winstable' => $winstable,
			'winpre' => $winpre,
			'osxstable' => $osxstable,
			'osxpre' => $osxpre,
			'linstable' => $linstable,
			'linpre' => $linpre
		];
	} catch (Exception $e) {
		error_log($e);
		return $app['twig']->render('download/error.twig');
	}

	return $app['twig']->render('download/index.twig', $vars);
}

require_once('artwork.php');
$app['twig']->addFunction(new Twig_SimpleFunction('create_artwork_item', 'create_artwork_item', ['is_safe' => ['html']]));
function artworkPage()
{
	global $app;
	return $app['twig']->render('download/artwork.twig');
}

/* Home page */
require_once('utils.php');
$app['twig']->addFunction(new Twig_SimpleFunction('youtube_iframe', 'youtube_iframe', ['is_safe' => ['html']]));
function homePage()
{
	global $app;
	return $app['twig']->render('home.twig');
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
