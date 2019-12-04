<?php
namespace App;
use LMMS\Navbar;

// require_once('i18n.php');
class TopNav
{
	public function __construct()
	{
		$this->navbar = new Navbar(
			[
				[_('Download'), '/download'],
				[_('Get Involved'), '/get-involved'],
				[[_('Documentation'), _('Docs')], '/documentation'],
				[_('Forum'), '/forum'],
				[[_('Sharing Platform'), _('Share')], '/lsp'],
				[_('More'), null, [
					['fa-eye', _('Showcase'), '/showcase'],
					['fa-trophy', _('Competitions'), '/competitions'],
					['fa-tags', _('Branding'), '/branding']]],
				// $i18n->langDropdown()
			]
		);
	}

	public function get()
	{
		return $this->navbar;
	}
}
