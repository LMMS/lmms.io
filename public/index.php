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
$app->get('/community/', function () use($app) {
	require_once('community.php');
	return '';
});
$app->get('/documentation/', 'documentationPage');
$app->get('/documentation/{page}', 'documentationPage');
$app->get('/download/', 'downloadPage');
$app->get('/download/artwork', twigrender('download/artwork.twig'));
$app->get('/download/samples', twigrender('download/samples.twig'));
$app->get('/get-involved/', twigrender('get-involved.twig'));
$app->get('/screenshots/', 'screenshotsPage');
$app->get('/showcase/', twigrender('showcase.twig'));

$uri = $_SERVER['REQUEST_URI'];

$app->run();
