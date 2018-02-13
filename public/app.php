<?php
require_once($_SERVER['DOCUMENT_ROOT'].'./../vendor/autoload.php');
require_once('i18n.php');

$i18n = new LMMSI18N();

require_once('navbar.php');

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
    ));

$app['twig']->addGlobal('navbar', $navbar);
$app['twig']->addGlobal('locale', $i18n->getLanguage());

require_once($_SERVER['DOCUMENT_ROOT'].'./../lib/GitHubMarkdownEngine.php');
use Aptoma\Twig\Extension\MarkdownExtension;


$app['twig']->addExtension(new MarkdownExtension(new GitHubMarkdownEngine()));
$app['twig']->addExtension(new Twig_Extensions_Extension_I18n());
