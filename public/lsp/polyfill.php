<?php
// The following hack is necessary to get Twig templates rendered
// Hacks start
require_once('../../vendor/autoload.php');
require_once('../../src/TopNav.php');
use Symfony\Component\Translation\Translator;
global $twig;
$loader = new \Twig\Loader\FilesystemLoader('../../templates/');
$twig = new \Twig\Environment($loader, [
    'cache' => $_SERVER["DOCUMENT_ROOT"] . '/../var/cache/lsp',
]);
$tr = new Translator("en");
$nav = new App\TopNav($tr);
$mock_app = ["request" => ["pathinfo" => $_SERVER["REQUEST_URI"]]];
$twig->addGlobal('navbar', $nav);
$twig->addGlobal('app', $mock_app);
$twig->addExtension(new Twig_Extensions_Extension_I18n());
// Hacks end
