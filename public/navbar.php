<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/Navbar.php');
$navbar = new Navbar(
	[
		['Download', '/download/'],
		['Get Involved', '/get-involved/'],
		[['Documentation', 'Docs'], '/documentation/'],
		['Forum', '/forum/'],
		[['Sharing Platform', 'Share'], '/lsp/'],
		['More', '/nonsence/', [
			['fa-eye', 'Showcase', '/showcase/'],
			['fa-trophy', 'Competitions', '/competitions/'],
			['fa-tags', 'Branding', '/branding']]],
	]
);
