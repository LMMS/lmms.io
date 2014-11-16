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

$pages = [
	['/', twigrender('home.twig')],
	['/community/', function () use($app) {
		ob_start();
		require_once('community.php');
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}],
	['/documentation/', 'documentationPage'],
	['/documentation/{page}', 'documentationPage'],
	['/download/', 'downloadPage'],
	['/download/artwork', twigrender('download/artwork.twig')],
	['/download/samples', twigrender('download/samples.twig')],
	['/get-involved/', twigrender('get-involved.twig')],
	['/screenshots/', 'screenshotsPage'],
	['/showcase/', twigrender('showcase.twig')]
];

foreach ($pages as $page) {
	$app->get($page[0], $page[1]);
}

$app->run();
