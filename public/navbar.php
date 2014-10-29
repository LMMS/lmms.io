<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/Navbar.php');
$navbar = new Navbar(
	[
		['Download', '/download/', [
			['fa-download', 'Download LMMS', '/download/'],
			['fa-music', 'Download Sample Packs', '/download/samples'],
			['fa-picture-o', 'Download Artwork', '/download/artwork']]],
		[['Screenshots', 'Screens'], '/screenshots/'],
		['Get Involved', '/get-involved/'],
		['Showcase', '/showcase/'],
		[['Documentation', 'Docs'], '/documentation/'],
		['Community', '/community/', [
			['fa-users', 'Community', '/community/'],
			['fa-comments', 'Forums', '/forum/'],
			['fa-facebook', 'Facebook', '/community/#facebook'],
			['fa-soundcloud', 'SoundCloud', '/community/#soundcloud'],
			['fa-google-plus','Google+', '/community/#google+'],
			['fa-youtube', 'YouTube', '/community/#youtube'],
			['fa-github', 'GitHub', '/community/#github']]],
		['Share', '/lsp/'],
	]
);
