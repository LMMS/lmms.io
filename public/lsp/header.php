<?php
require_once('../../vendor/autoload.php');
require_once('../../src/TopNav.php');
$loader = new \Twig\Loader\FilesystemLoader('../../templates/');
$twig = new \Twig\Environment($loader, [
    'cache' => $_SERVER["DOCUMENT_ROOT"] . '/../tmp/',
]);
$nav = new App\TopNav();
$twig->addGlobal('navbar', $nav);
echo $twig->render('head.twig');
?>
<div class="jumbotron mini">
	<div class="container">
		<h2>LMMS Sharing Platform</h2>
    <h5>Share your work with the LMMS community.</h5>
	</div>
</div>
<div class="container theme-showcase main-div" role="main">
