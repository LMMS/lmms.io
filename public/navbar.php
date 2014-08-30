<?php
include_once('utils.php');
function create_navbar() {
?>
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/"><img class="logo-sm pull-left" src="/img/logo_sm.png" />LMMS</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<?php
						menu_item('Home');
						@dropdown_menu_item('Download', '<span class="fa fa-download fa-fw"></span>Download LMMS', '/download/', array(
							array('<span class="fa fa-music fa-fw"></span> Download Sample Packs', '/download/samples/'),
							array('<span class="fa fa-picture-o fa-fw"></span> Download Artwork', '/download/artwork/')
						));
						menu_item('Screenshots');
						menu_item('Showcase');
						menu_item('Documentation');
						@dropdown_menu_item('Community', '<span class="fa fa-users fa-fw"></span> Community', '/community/', array(
							array('<span class="fa fa-comments fa-fw"></span> Forums', '/forum/'),
							array('<span class="fa fa-facebook fa-fw"></span> Facebook', '/community/#facebook'),
							array('<span class="fa fa-soundcloud fa-fw"></span> SoundCloud', '/community/#soundcloud'),
							array('<span class="fa fa-google-plus fa-fw"></span> Google+', '/community/#google+'),
							array('<span class="fa fa-youtube fa-fw"></span> YouTube', '/community/#youtube'),
							array('<span class="fa fa-github fa-fw"></span> GitHub', '/community/#github')
						));
						menu_item('Share', '/lsp/');
					?>
				</ul>
			</div>
		</div>
	</nav>
<?php
}

/*
 * Returns the current page name, i.e. "Home", etc
 */
function get_page_name() {
	if (str_startswith($_SERVER["REQUEST_URI"], '/lsp/')) {
		return "Share";
	}

	if (str_startswith($_SERVER["REQUEST_URI"], '/forum/')) {
		return 'Community';
	}

	$uri = trim($_SERVER["REQUEST_URI"], "/");

	switch($uri) {
		case '':
		case 'header.php':
		case 'index.php':
			return 'Home';
		case 'download/artwork':
		case 'download/samples':
		case 'download':
					return 'Download';
		case 'lsp':
			return 'Share';
		default:
			return preg_replace('/\.[^.]*$/', '', ucfirst($uri));
	}
}

/*
 * Creates a simple tag <li><a href="menu_item.php">Menu Item</a></li>
 * Taking into consideration the "active" status/style
 */
function menu_item($text, $url = NULL, $dropdown = NULL, $disabled = NULL, $class = NULL) {
	$class .= mini_me($text, $url, $dropdown, $disabled, $class);
	
	// Determine the "Active Tab
	if ($text == get_page_name()) {
		$active = 'active';
	} else {
		$active = '';
	}

	if (is_null($url)) {
		if ($text == "Home") $url = '/';
		else $url = '/' . strtolower($text) . '/';
	}
	if ($dropdown) {
		// Important - This leaves an open <li> tag.  Must be closed manually.
		echo '<li class="' . $active . ' ' . $class . '"><a href="' . $url . '" class="dropdown-toggle' . ($disabled ? ' disabled' : '') . '" data-toggle="dropdown"><span class="caret"></span></a>';
	} else {
		echo '<li class="' . $active . ' ' . $class . ($disabled ? ' disabled' : '') . '"><a href="' . $url . '">' . $text . '</a></li>';
	}
}

/*
 * Creates a second menu item with abbreviated text visible at medium sized screens
 * to help prevent overflow in the navbar
 */
function mini_me($text, $url, $dropdown, $disabled, $class) {
	switch ($text) {
		case "Screenshots":
			menu_item("Screens", "/screenshots/", $dropdown, $disabled, $class . ' hidden-lg');
			return ' visible-lg';
		case "Documentation":
			menu_item("Docs", "/documentation/", $dropdown, $disabled, $class . ' hidden-lg');
			return ' visible-lg';
		default: return '';
	}
}

/*
 * Creates a split dropdown item for big screens and a normal dropdown for small
 * screens. As a split dropdown is not possible for small screens and as it's
 * more handy this way, the main link is repeated inside the dropdown.
 * This is what $alttext is for.
 * Example: dropdown_menu_item('Download', 'Download LMMS', '/download.php', …)
 */
function dropdown_menu_item($text, $alttext, $url, $items) {
	// Determine the "Active Tab
	if ($text == get_page_name()) {
		$active = 'active';
	} else {
		$active = '';
	}

	// Dropdown for big screens
	echo "<li class='dropdown-split-left hidden-xs $active'> <a href='$url'>$text</a> </li>";
	echo "<li class='dropdown-split-right hidden-xs $active'> <a href='#' class='dropdown-toggle' data-toggle='dropdown'> <span class='caret'></span></a>";
	echo "<ul class='dropdown-menu pull-right'>";

	echo "<li><a href='$url'>" . ($alttext ? $alttext : $text) . "</a></li>";
	echo "<li class='divider'></li>";
	foreach ($items as $item) {
		$target = (str_startswith($item[1], 'http://') || str_startswith($item[1], 'https://')) ? '_blank' : '_self';
		echo "<li><a href='$item[1]' target='$target'>$item[0]</a></li>";
	}

	echo "</ul> </li>";

	// Dropdown for small screens

	echo "<li class='dropdown visible-xs $active'> <a href='#' class='dropdown-toggle' data-toggle='dropdown'>$text <span class='caret'></span></a>";
	echo "<ul class='dropdown-menu' role='menu'>";

	echo "<li><a href='$url'>" . ($alttext ? $alttext : $text) . "</a></li>";
	foreach ($items as $item) {
		echo "<li><a href=$item[1]>$item[0]</a></li>";
	}
	echo "</ul></li>";
}

?>
