<?php
require_once('force-https.php');
require_once('app.php');
use Symfony\Component\HttpFoundation\Response;

function twigrender($file)
{
	global $app;
	return function() use ($app, $file) {
		return $app['twig']->render($file);
	};
}

require_once('../views.php');
$pages = [
	['/', 'homePage'],
	['/documentation/', 'documentationPage'],
	['/documentation/{page}', 'documentationPage'],
	['/download/', 'downloadPage'],
//	['/download/samples/', twigrender('download/samples.twig')],
	['/get-involved/', twigrender('get-involved.twig')],
	['/showcase/', twigrender('showcase.twig')],
	['/competitions/', twigrender('competitions.twig')],
	['/branding/', twigrender('branding.twig')]
];

foreach ($pages as $page) {
	$app->get($page[0], $page[1]);
}

$app->error(function (\Exception $e, $code) use($app) {
	switch ($code) {
		case 404:
			$message = _('The requested page could not be found.');
			break;
		default:
			$message = _('We are sorry, but something went terribly wrong.');
	}

	$GLOBALS['pagetitle'] = _('Yuck, an error!');
	return $app['twig']->render('errorpage.twig', [
		'message' => $message,
		'code' => $code
	]);
});

$app->run();
