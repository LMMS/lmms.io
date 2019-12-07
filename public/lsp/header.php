<?php
// The following hack is necessary to get Twig templates rendered
// Hacks start
require_once('../../vendor/autoload.php');
require_once('../../src/TopNav.php');
use Symfony\Component\Translation\Translator;
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
echo $twig->render('head.twig');
?>
<div class="jumbotron mini">
	<div class="container">
		<h2>LMMS Sharing Platform</h2>
    <h5>Share your work with the LMMS community.</h5>
	</div>
</div>
<div class="container theme-showcase main-div" role="main">
