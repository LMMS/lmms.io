<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/Navbar.php');
require_once('i18n.php');
$navbar = new Navbar(
	[
		[_('Download'), '/download/'],
		[_('Get Involved'), '/get-involved/'],
		[[_('Documentation'), _('Docs')], '/documentation/'],
		[_('Forum'), '/forum/'],
		[[_('Sharing Platform'), _('Share')], '/lsp/'],
		[_('More'), '/nonsence/', [
			['fa-eye', _('Showcase'), '/showcase/'],
			['fa-trophy', _('Competitions'), '/competitions/'],
			['fa-tags', _('Branding'), '/branding']]],
	]
);
