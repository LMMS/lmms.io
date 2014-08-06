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
						menu_item('Home', '/index.php');
						menu_item('Download', '/download.php', false, false, 'dropdown-split-left');
						menu_item('Download', '#', true, false, 'dropdown-split-right hidden-xs');
						echo '<ul class="dropdown-menu pull-right">';
							menu_item('<span class="fa fa-download"></span> Download LMMS', '/download.php');
							menu_item('<span class="fa fa-music"></span> Download Sample Packs', '#', false, true);
							menu_item('<span class="fa fa-picture-o"></span> Download Artwork', '/artwork.php');
						// Important! - Make sure to close the parent list item tag with "</li>"
						echo '</ul></li>';
						menu_item('Screenshots');
						menu_item('Tracks');
						menu_item('Documentation');
						menu_item('Community');
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
	$uri = str_replace('/', '', $_SERVER["REQUEST_URI"]);
	
	if (str_contains($uri, '/forum/')) {
		return 'Community';
	}
	
	switch($uri) {
		case 'header.php':
		case '':
		case 'index.php':
		case 'home.php':
			return 'Home';
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
		$url = '/' . strtolower($text) . '.php';
	}
	if ($dropdown) {
		// Important - This leaves an open <li> tag.  Must be closed manually.
		echo '<li class="' . $active . ' ' . $class . '"><a href="' . $url . '" class="dropdown-toggle' . ($disabled ? ' disabled' : '') . '" data-toggle="dropdown"><span class="caret"></span></a>';
	} else {
		echo '<li class="' . $active . ' ' . $class . ($disabled ? ' disabled' : '') . '"><a href="' . $url . '">' . $text . '</a></li>';
	}
}

function str_contains($haystack, $needle) {
	return strpos($haystack, $needle) !== FALSE;
}

?>
