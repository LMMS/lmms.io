<?php
namespace App;

class TopNav
{
	public function __construct()
	{
		$this->navbar = [
			['Download', '/download'],
			['Get Involved', '/get-involved'],
			[['Documentation', 'Docs'], '/documentation'],
			['Forum', '/forum'],
			[['Sharing Platform', 'Share'], '/lsp'],
			['More', null, [
				['fa-eye', 'Showcase', '/showcase'],
				['fa-trophy', 'Competitions', '/competitions'],
				['fa-tags', 'Branding', '/branding']]],
			];
	}

	public function get()
	{
		return $this->navbar;
	}
}
