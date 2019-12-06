<?php
namespace LMMS;
require_once($_SERVER["DOCUMENT_ROOT"] . '/../public/utils.php');
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
	 *		(e.g. ['Screenshots, Screens'])
	 * @param string $url	The item's target URL, e.g. '/screenshots/'
	 * @param array|null $children	Can be either
	 *		(1) null for a normal menu item or
	 *		(2) A two-dimensional array listing subitems in a dropdown.
	 *			Each item in the array must have format [$icon, $text, $url]
	 *			with $icon being null or a FontAwesome icon class
	 * @param boolval $rightAlign Sets wether the item is right aligned
	 */
	public function __construct($text, $url, $children = null, $rightAlign = false)
	{
		$this->text = $text;
		$this->url = $url;
		$this->children = $children;
		$this->active = '';
		$this->rightAlign = $rightAlign;
	}

	/**
	 * Checks if this item is the currently displayed page, i.e. should be
	 * displayed as active item in the menu.
	 *
	 * @return bool
	 */
	public function isActive(string $req_uri)
	{
		if ($this->url == '/') {
			return $this->url == $req_uri;
		}

		if (str_startswith($req_uri, $this->url)) {
			$this->active = 'active';
			return true;
		} elseif ($this->children) {
			foreach ($this->children as $child) {
				if (str_startswith($req_uri, $child[2])) {
					$this->active = 'active';
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

			echo "<li tabindex='0' class='dropdown hidden-xs'>
					<a href='#'>$this->text <span class='caret'></span></a>";
			echo "<ul tabindex='0' class='dropdown-menu pull-right'>";
			foreach ($this->children as $key => $child) {
				$this->printItem('', $child[1], $child[2], $child[0]);
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
			$text = "<i class='fas $icon fa-fw'></i> $text";
		}
		echo "<li class='$class'> <a href='$url'>$text</a></li>";
	}

	public $text;
	public $url;
	public $children;
	private $active;
}
