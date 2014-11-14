<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once('navbar.php');

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../templates',
	));

$app['twig']->addGlobal('navbar', $navbar);

$app['debug'] = true;

function twigrender($file)
{
	global $app;
	return function() use ($app, $file) {
		return $app['twig']->render($file);
	};
}

require_once('../views.php');
// Set up routes
$app->get('/', twigrender('home.twig'));
$app->get('/get-involved/', twigrender('get-involved.twig'));
$app->get('/documentation/', 'documentationPage');
$app->get('/documentation/{page}', 'documentationPage');
$app->get('/download/', 'downloadPage');
$app->get('/download/artwork', twigrender('download/artwork.twig'));
$app->get('/download/samples', twigrender('download/samples.twig'));
$app->get('/showcase/', twigrender('showcase.twig'));
$app->get('/screenshots/', 'screenshotsPage');

$uri = $_SERVER['REQUEST_URI'];

// Each item like: 'Page title' => [ 'URL (opt. regex)', 'page php file' ]
$pages = [
	'Community' => ['/community', 'community.php']
];

// Loop all pages to find the one requested
foreach ($pages as $key => $page) {
	$regex = $page[0];
	$file = $page[1];

	// Escape all '/' characters.
	$regex = str_replace('/', '\/', $regex);
	// Add an optional '/' at the end of the URL; Ignore case
	$regex = '/^' . $regex . '(\/)?$/i';

	if (preg_match($regex, $uri)) {
		// If this is the requested page, set the global pagetitle to be used by the navbar
		$GLOBALS['pagetitle'] = $key;

		// Include the page's php file and exit
		require_once($file);
		exit();
	}
}

$app->run();
