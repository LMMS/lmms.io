<?php
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
				<a class="navbar-brand" href="/index.php"><img class="visible-lg logo-sm" style="float: left;" src="/img/logo_sm.png" />LMMS</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<?php
						menu_item('Home');
						@dropdown_menu_item('Download', '<span class="fa fa-download fa-fw"></span>Download LMMS', '/download.php', array(
							//array('<span class="fa fa-download fa-fw"></span> Download LMMS', '/download.php'),
							array('<span class="fa fa-music fa-fw"></span> Download Sample Packs', '#'),
							array('<span class="fa fa-picture-o fa-fw"></span> Download Artwork', '/artwork.php')
						));
						menu_item('Screenshots');
						menu_item('Tracks');
						menu_item('Documentation');
						@dropdown_menu_item('Community', '<span class="fa fa-users fa-fw"></span> Community', '/community.php', array(
							array('<span class="fa fa-comments fa-fw"></span> Forums', '/forums/'),
							array('<span class="fa fa-facebook fa-fw"></span> Facebook', 'https://www.facebook.com/makefreemusic'),
							array('<span class="fa fa-soundcloud fa-fw"></span> SoundCloud', 'https://soundcloud.com/groups/linux-multimedia-studio'),
							array('<span class="fa fa-google-plus fa-fw"></span> Google+', 'https://plus.google.com/u/0/113001340835122723950/posts'),
							//array('<span class="fa fa-youtube fa-fw"></span> YouTube', '#'),
							array('<span class="fa fa-github fa-fw"></span> GitHub', 'https://github.com/LMMS/lmms')
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
	if (str_startswith($_SERVER["REQUEST_URI"], '/forum/')) {
		return 'Community';
	}

	$uri = str_replace('/', '', $_SERVER["REQUEST_URI"]);


	switch($uri) {
		case 'header.php':
		case '':
		case 'index.php':
		case 'home.php':
			return 'Home';
		case 'artwork.php':
		case 'samples.php':
		case 'samplepacks.php':
		case 'download.php':
					return 'Download';
		default:
			return preg_replace('/\.[^.]*$/', '', ucfirst($uri));
	}
}

/*
 * Creates a simple tag <li><a href="menu_item.php">Menu Item</a></li>
 * Taking into consideration the "active" status/style
 */
function menu_item($text, $url = NULL, $dropdown = NULL, $disabled = NULL, $class = NULL) {
	// Determine the "Active Tab
	if ($text == get_page_name()) {
		$active = 'active';
	} else {
		$active = '';
	}

	if (is_null($url)) {
		$url = '/' . strtolower($text == "Home" ? "Index" : $text) . '.php';
	}
	if ($dropdown) {
		// Important - This leaves an open <li> tag.  Must be closed manually.
		echo '<li class="' . $active . ' ' . $class . '"><a href="' . $url . '" class="dropdown-toggle' . ($disabled ? ' disabled' : '') . '" data-toggle="dropdown"><span class="caret"></span></a>';
	} else {
		echo '<li class="' . $active . ' ' . $class . ($disabled ? ' disabled' : '') . '"><a href="' . $url . '">' . $text . '</a></li>';
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
	echo "<li class='dropdown-split-left hidden-xs'> <a href='$url'>$text</a> </li>";
	echo "<li class='dropdown-split-right hidden-xs'> <a href='#' class='dropdown-toggle' data-toggle='dropdown'> <span class='caret'></span></a>";
	echo "<ul class='dropdown-menu pull-right'>";

	echo "<li><a href='$url'>" . ($alttext ? $alttext : $text) . "</a></li>";
	echo "<li class='divider'></li>";
	foreach ($items as $item) {
		echo "<li><a href='$item[1]'>$item[0]</a></li>";
	}

	echo "</ul> </li>";

	// Dropdown for small screens

	echo "<li class='dropdown visible-xs'> <a href='#' class='dropdown-toggle' data-toggle='dropdown'>$text <span class='caret'></span></a>";
	echo "<ul class='dropdown-menu' role='menu'>";

	echo "<li><a href='$url'>" . ($alttext ? $alttext : $text) . "</a></li>";
	foreach ($items as $item) {
		echo "<li><a href=$item[1]>$item[0]</a></li>";
	}
	echo "</ul></li>";
}

function str_contains($haystack, $needle) {
	return strpos($haystack, $needle) !== FALSE;
}

function str_startswith($haystack, $needle) {
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function str_endswith($haystack, $needle) {
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

?>
