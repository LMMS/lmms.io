<?php
namespace App;

use Symfony\Contracts\Translation\TranslatorInterface;
class TopNav
{
	public function __construct(TranslatorInterface $tr)
	{
		$this->navbar = [
			[$tr->trans('News'), '/news'],
			[$tr->trans('Download'), '/download'],
			[$tr->trans('Get Involved'), '/get-involved'],
			[[$tr->trans('Documentation'), $tr->trans('Docs')], '/documentation'],
			[$tr->trans('Forum'), '/forum'],
			[[$tr->trans('Sharing Platform'), $tr->trans('Share')], '/lsp'],
			[$tr->trans('More'), null, [
				['fa-eye', $tr->trans('Showcase'), '/showcase'],
				['fa-trophy', $tr->trans('Competitions'), '/competitions'],
				['fa-tags', $tr->trans('Branding'), '/branding']]],
			];
	}

	public function get(): array
    {
		return $this->navbar;
	}

    private array $navbar;
}
