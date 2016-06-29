<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/utils.php');

/**
 * Class representing an item in the navbar. This class is not intended to be
 * used directly
 */

class MenuItem
{
	/**
	 * Creates a new MenuItem.
	 *
	 * @param mixed $text	Title of the item as displayed in the menu bar. Can
	 * 		be either string or array with long and short name
	 *		(e.g. ['Screenshots, Sreens'])
	 * @param string $url	The item's target URL, e.g. '/screenshots/'
	 * @param array|null $children	Can be either
	 *		(1) null for a normal menu item or
	 *		(2) A two-dimensional array listing subitems in a dropdown.
	 *			Each item in the array must have format [$icon, $text, $url]
	 *			with $icon being null or a FontAwesome icon class
	 */
	public function __construct($text, $url, $children = null)
	{
		$this->text = $text;
		$this->url = $url;
		$this->children = $children;
		$this->active = $this->isActive() ? 'active' : '';
	}

	/**
	 * Checks if this item is the currently displayed page, i.e. should be
	 * displayed as active item in the menu.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		$req_uri = $_SERVER["REQUEST_URI"];
		if ($this->url == '/') {
			return $this->url == $req_uri;
		}

		if (str_startswith($req_uri, $this->url)) {
			return true;
		} elseif ($this->children) {
			foreach ($this->children as $child) {
				if (str_startswith($req_uri, $child[2])) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the menu item's title. If the has two titles, the long one is
	 * returned.
	 */
	public function getTitle()
	{
		if (is_array($this->text)) {
			return $this->text[0];
		} else {
			return $this->text;
		}
	}

	/**
	 * Prints the item
	 */
	public function flush()
	{
		if ($this->children == null) {
			// Simple menu item. If $text is an array, $text[0] is the title displayed
			// on big screens, $text[1] is the one displayed on smaller screens
			if (is_array($this->text)) {
				$this->printItem("$this->active visible-lg", $this->text[0], $this->url);
				$this->printItem("$this->active hidden-lg", $this->text[1], $this->url);
			} else {
				$this->printItem($this->active, $this->text, $this->url);
			}
		} else {
			// Dropdown item

			// Split dropdown for big screens
			echo "<li class='dropdown-split-left $this->active hidden-xs'> <a href='$this->url'>$this->text</a> </li>";
			echo "<li class='dropdown-split-right $this->active hidden-xs'>
					<a href='#' class='dropdown-toggle' data-toggle='dropdown'> <span class='caret'></span></a>";
			echo "<ul class='dropdown-menu pull-right'>";
			foreach ($this->children as $key => $child) {
				$this->printItem('', $child[1], $child[2], $child[0]);
				if ($key == 0) {
					echo "<li class='divider'></li>";
				}
			}
			echo '</ul></li>';

			// Normal dropdown for small screens (collapsed navbar)
			echo "<li class='$this->active visible-xs'>
				<a href='#' class='dropdown-toggle' data-toggle='dropdown'>$this->text <i class='caret'></i></a>";
			echo '<ul class="dropdown-menu" role="menu">';
			foreach ($this->children as $child) {
				$this->printItem('', $child[1], $child[2], $child[0]);
			}
			echo '</ul></li>';
		}
	}

	/**
	 * Private helper function. Prints a basic item with the given
	 * $text, $url, $class (CSS) and an optional FontAwesome $icon.
	 */
	private function printItem($class, $text, $url, $icon = null)
	{
		if ($icon) {
			$text = "<i class='fa $icon fa-fw'></i> $text";
		}
		echo "<li class='$class'> <a href='$url'>$text</a></li>";
	}

	public $text;
	public $url;
	public $children;
	private $active;
}

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
			$this->items[] = new MenuItem($item[0], $item[1], count($item)>2 ? $item[2] : null);
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

		foreach ($this->items as $item) {
			if ($item->isActive()) {
				return $item->getTitle();
			}
		}
		if ($_SERVER["REQUEST_URI"] === '/' or
			$_SERVER["REQUEST_URI"] === '/index.php')
			return 'Home';
	}

	/**
	 * Prints the navbar.
	 */
	public function flush()
	{
		?>
		<nav class="navbar navbar-custom navbar-static-top" role="navigation">
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
			$item->flush();
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
