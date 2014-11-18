<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once('navbar.php');

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../templates',
	));

$app['twig']->addGlobal('navbar', $navbar);
