<?php
require_once('../vendor/autoload.php');
require_once('utils.php');

$uri = $_SERVER['REQUEST_URI'];

// Each item like: 'Page title' => [ 'URL (opt. regex)', 'page php file' ]
$pages = [
	'Home' => ['/', 'home.php'],
	'Documentation' => ['/documentation(/.*)?', 'documentation.php'],
	'Get Involved' => ['/get-involved', 'get-involved.html'],
	'Community' => ['/community', 'community.php'],
	'Screenshots' => ['/screenshots', 'screenshots.php'],
	'Showcase' => ['/showcase', 'showcase.html'],
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
		} elseif (str_endswith($file, '.html')) {
			require_once('navbar.php');

			$loader = new Twig_Loader_Filesystem('../templates');
			$twig = new Twig_Environment($loader, array(
				//'cache' => '/path/to/compilation_cache',
			));
			echo $twig->render($file, ['navbar' => $navbar]);
			exit();
		}
	}
}

// If no page is found, load 404
require_once('404.php');
