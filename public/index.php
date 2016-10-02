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
require_once('../hooks/github.php');

$pages = [
	['/', 'homePage'],
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
	['/download/artwork/', twigrender('download/artwork.twig')],
//	['/download/samples/', twigrender('download/samples.twig')],
	['/get-involved/', twigrender('get-involved.twig')],
	['/showcase/', twigrender('showcase.twig')],
	['/competitions/', twigrender('competitions.twig')]
];

foreach ($pages as $page) {
	$app->get($page[0], $page[1]);
}

$app->post('/hooks/github/', 'githubHook');

$app->error(function (\Exception $e, $code) use($app) {
	error_log($e);
	switch ($code) {
		case 404:
			$message = 'The requested page could not be found.';
			break;
		default:
			$message = 'We are sorry, but something went terribly wrong.';
	}

	$GLOBALS['pagetitle'] = 'Yuck, an error!';
	return $app['twig']->render('errorpage.twig', [
		'message' => $message,
		'code' => $code
	]);
});

$app->run();
