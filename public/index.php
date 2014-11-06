<?php

$uri = $_SERVER['REQUEST_URI'];

$patterns = [
	'Home' => ['/(home)?', 'home.php']
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
