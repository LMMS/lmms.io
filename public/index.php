<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/utils.php');

$uri = $_SERVER['REQUEST_URI'];

// Each item like: 'Page title' => [ 'URL (opt. regex)', 'page php file' ]
$pages = [
	'Home' => ['/', 'home.twig'],
	'Documentation' => ['/documentation(/.*)?', 'documentation.php'],
	'Get Involved' => ['/get-involved', 'get-involved.twig'],
	'Community' => ['/community', 'community.php'],
	'Screenshots' => ['/screenshots', 'screenshots.php'],
	'Showcase' => ['/showcase', 'showcase.twig'],
	'Download' => ['/download', 'download.php']
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

		if (str_endswith($file, '.php')) {
			// Include the page's php file and exit
			require_once($file);
			exit();
		} elseif (str_endswith($file, '.twig')) {
			require_once('navbar.php');

			$loader = new Twig_Loader_Filesystem('../templates');
			$twig = new Twig_Environment($loader, array(
				//'cache' => '/path/to/compilation_cache',
			));

			$safehtml = ['is_safe' => ['html']];
			$twig->addFunction(new Twig_SimpleFunction('icon', 'icon', $safehtml));
			$twig->addFunction(new Twig_SimpleFunction('icon_stack', 'icon_stack', $safehtml));
			$twig->addFunction(new Twig_SimpleFunction('circle_stack', 'circle_stack', $safehtml));
			$twig->addFunction(new Twig_SimpleFunction('make_reflection', 'make_reflection', $safehtml));


			echo $twig->render($file, ['navbar' => $navbar]);
			exit();
		}
	}
}

// If no page is found, load 404
require_once('404.php');
