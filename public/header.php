<?php
require_once('navbar.php');
?>
<!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>LMMS &bull; <?php echo $navbar->activePageTitle(); ?></title>

		<link rel="icon" type="image/png" href="/img/logo_sm.png">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link href="//cdn.rawgit.com/Lukas-W/font-linux/v0.1/assets/font-linux.css" rel="stylesheet">
		<link href='//fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
		<link href="/css/style.css" rel="stylesheet">
		<link href="/css/lightbox.css" rel="stylesheet">

		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="/js/lightbox.min.js"></script>
	</head>

	<body role="document">
		<?php $navbar->flush();
		// This is quite dirty, couldn't think of a better solution
		if ($navbar->activePageTitle() === 'Home') {
			?>
		<div class="jumbotron jumbo">
			<div class="container">

				<?php make_reflection('img/ss_proj.png', null, "black", "pull-right visible-lg"); ?>

				<h1 class="jumbo">Let's make music</h1>
				<p class="jumbo">with a free, cross-platform tool for your computer.</p><br>
				<a class="btn btn-primary btn-lg" href="https://www.youtube.com/watch?v=uWPfIIaAHQg" target="_blank" role="button">See how »</a>&nbsp;
				<a class="btn btn-primary btn-lg" href="/download/" role="button">Download now »</a>&nbsp;
				<a class="btn btn-primary btn-lg" href="/get-involved/" role="button">Get involved »</a>
			</div>
		</div>
		<?php } ?>
		<div class="container theme-showcase main-div" role="main">
