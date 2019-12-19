<?php
// The following hack is necessary to get Twig templates rendered
// Hacks start
require_once('../../vendor/autoload.php');
require_once('polyfill.php');
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
