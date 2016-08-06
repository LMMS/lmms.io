<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/Navbar.php');
$navbar = new Navbar(
	[
		['Download', '/download/'],
		['Get Involved', '/get-involved/'],
		['Showcase', '/showcase/'],
		[['Documentation', 'Docs'], '/documentation/'],
		['Community', '/community/', [
			['fa-trophy', 'Competitions', '/competitions/'],
			['fa-comments', 'Forums', '/forum/'],
			['fa-share-alt', 'Sharing Platform', '/lsp/']]],
	]
);
