<?php
namespace App;

use Symfony\Contracts\Translation\TranslatorInterface;

enum TopNavItemType {
	case Link;
	case Dropdown;
	case ThemeToggle;
}

class TopNav
{
	public function __construct(TranslatorInterface $tr)
	{
		$this->navbar = [
			[$tr->trans('News'), '/news', TopNavItemType::Link],
			[$tr->trans('Download'), '/download', TopNavItemType::Link],
			[$tr->trans('Get Involved'), '/get-involved', TopNavItemType::Link],
			[[$tr->trans('Documentation'), $tr->trans('Docs')], '/documentation', TopNavItemType::Link],
			[$tr->trans('Forum'), '/forum', TopNavItemType::Link],
			[[$tr->trans('Sharing Platform'), $tr->trans('Share')], '/lsp', TopNavItemType::Link],
			[$tr->trans('More'), null, [
				['fa-eye', $tr->trans('Showcase'), '/showcase'],
				['fa-trophy', $tr->trans('Competitions'), '/competitions'],
				['fa-tags', $tr->trans('Branding'), '/branding']], TopNavItemType::Link],
			[$tr->trans('Theme'), null, TopNavItemType::Link]
			];
	}

	public function get(): array
    {
		return $this->navbar;
	}

    private array $navbar;
}
