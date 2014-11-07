<?php

$uri = $_SERVER['REQUEST_URI'];

$patterns = [
	'Home' => ['/', 'home.php'],
	'Documentation' => ['/documentation(/.*)?', 'documentation.php'],
	'Get Involved' => ['/get-involved', 'get-involved.php'],
	'Community' => ['/community', 'community.php'],
	'Screenshots' => ['/screenshots', 'screenshots.php'],
	'Showcase' => ['/showcase', 'showcase.php'],
	'Download' => ['/download', 'download.php']
];

foreach ($patterns as $key => $value) {
	$regex = $value[0];
	$file = $value[1];

	$regex = str_replace('/', '\/', $regex);
	$regex = '/^' . $regex . '(\/)?$/i';

	if (preg_match($regex, $uri)) {
		require_once($file);
		exit();
	}
}

require_once('404.php');
