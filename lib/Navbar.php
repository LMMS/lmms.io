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
	public function activePageTitle()
	{
		if (array_key_exists('pagetitle', $GLOBALS))
		{
			return $GLOBALS['pagetitle'];
		}

		$pageURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		if ($pageURI === '/' or $pageURI === '/index.php') return _('Home');

		foreach ($this->items as $item) {
			if ($item->isActive()) {
				return $item->getTitle();
			}
		}
	}

	/**
	 * Prints the navbar.
	 */
	public function flush()
	{
		?>
		<nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="/"><img class="logo-sm pull-left" height="22px" width="22px" src="/img/brand-icon.png"></img>LMMS</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
		<?php
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
		?>
					</ul>
				</div>
			</div>
		</nav>
		<?php
	}

	private $items;
}
