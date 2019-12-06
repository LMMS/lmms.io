<?php
namespace LMMS;

/**
 * Class for creating and printing a Bootstrap navbar
 */
class Navbar
{
	/**
	 * Creates a new navbar.
	 *
	 * @param array $items An array of arrays containing the constructor
	 * arguments for MenuItems. See MenuItem->__construct(…) for the
	 * inner arrays' format.
	 */
	public function __construct($items)
	{
		foreach ($items as $item) {
			$this->items[] = new MenuItem($item[0], $item[1], count($item)>2 ? $item[2] : null, count($item)>3 ? $item[3] : null);
		}
	}

	/**
	 * Returns the currently viewed page's title.
	 */
	public function activePageTitle(string $pageURI = null)
	{
		if (array_key_exists('pagetitle', $GLOBALS))
		{
			return $GLOBALS['pagetitle'];
		}

		if ($pageURI === null)
			$pageURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		if ($pageURI === '/' or $pageURI === '/index.php') return _('Home');

		foreach ($this->items as $item) {
			if ($item->isActive($pageURI)) {
				return $item->getTitle();
			}
		}
	}

	/**
	 * Prints the navbar.
	 */
	public function flush()
	{
		foreach ($this->items as $item) {
			if (!$item->rightAlign) {
				$item->flush();
			}
		}
		?>
					</ul>
					<ul class="nav navbar-nav navbar-right">
		<?php
		foreach ($this->items as $item) {
			if ($item->rightAlign){
				$item->flush();
			}
		}
	}

	private $items;
}
