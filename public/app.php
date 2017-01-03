<?php
require_once($_SERVER['DOCUMENT_ROOT'].'./../vendor/autoload.php');
require_once('navbar.php');
require_once('i18n.php');

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
    ));

$app['twig']->addGlobal('navbar', $navbar);

require_once($_SERVER['DOCUMENT_ROOT'].'./../lib/GitHubMarkdownEngine.php');
use Aptoma\Twig\Extension\MarkdownExtension;

$i18n_date = new Twig_SimpleFilter('i18n_date', function ($date) {
  $real_date = DateTime::createFromFormat('!F*dS#?Y', $date);
  $timestamp = $real_date->format('U');
  return strftime('%c', $timestamp);
});

$app['twig']->addExtension(new MarkdownExtension(new GitHubMarkdownEngine()));
$app['twig']->addExtension(new Twig_Extensions_Extension_I18n());
$app['twig']->addFilter($i18n_date);
