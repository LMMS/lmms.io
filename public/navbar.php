<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/Navbar.php');
$navbar = new Navbar(
	[
		['Download', '/download/', [
			['fa-download', 'Download LMMS', '/download/'],
	//		['fa-music', 'Download Sample Packs', '/download/samples'],
			['fa-picture-o', 'Download Artwork', '/download/artwork']]],
		['Get Involved', '/get-involved/'],
		['Showcase', '/showcase/'],
		[['Documentation', 'Docs'], '/documentation/'],
		['Community', '/chat/', [
			['fa-comment','Discord Chat', '/chat/'],
			['fa-trophy', 'Competitions', '/competitions/'],
			['fa-list-alt', 'Forums', '/forum/'],
			['fa-facebook', 'Facebook', 'https://facebook.com/makefreemusic'],
			['fa-soundcloud', 'SoundCloud', 'https://soundcloud.com/search/sounds?q=%23lmms'],
			['fa-github', 'GitHub', 'https://github.com/lmms/lmms']]],
		['Share', '/lsp/'],
	]
);
