<?php

include_once('json_common.php');


/*
 * Default number of displayed items
 */
$max=1;

/*
 * Gets the past x number of releases in the specified format
 * $max_releases = 1, 2, 3, etc.
 * $format = "vert" returns vertical list of buttons (default), "horiz" returns horizontal buttons
 * $name_filter = ".exe", ".dmg", ".tar.gz", etc.
 * $repo = "lmms", "diizy", "Lukas-W", "tresf", etc.
 */
function get_releases($max_releases = NULL, $format = NULL, $name_filter = NULL, $repo = NULL) {

	/*
	 * Use the default value declared above if none is specified
	 */
	if (!$max_releases) {
		global $max;
		$max_releases = $max;
	}

	$delim = ($format == 'horiz' ? ' ' : '<br>');

	/*
	 * Creates an array of relational JSON objects from cached or online GitHub data
	 */
	$obj = get_json_data('github', 'releases', '', $repo);
	$count = 0;

	/*
	 * Loop through items and echo our data to the page
	 */
	if ($obj) {
		global $max;
		foreach($obj as $item) {
			$found = false;
			foreach($item->assets as $asset) {
				$text = $item->name . '&nbsp;(' . get_os_name($asset->name) . ')';

				$button_style = 'btn-default';
				$icon_style = 'text-primary';
				// Change to warning button for prerelease
				if ($item->prerelease) {
					$button_style = 'btn-default';
					$icon_style = 'text-warning';
					$text = $text . '&nbsp;<span class="fa fa-exclamation-circle text-warning"></span>';
				}

				// If no $name_filter is provided, echo.  If $name_filter is provided, filter based on name
				if (!$name_filter || ($name_filter && (strpos($asset->name,$name_filter) !== false))) {
					echo '<a data-dl-count="' . $asset->download_count . '" class="btn btn-lg dl-button ' . 
						$button_style . '" href="' . $asset->browser_download_url . 
						'"><span id="button-title">LMMS</span><br>' . 
						'<span class="fa fa-arrow-circle-down ' . $icon_style . '"></span><big>Free Download</big><br>' .
						'<small>' . $text . '</small></a>' . $delim;
					$found = true;
				}

				/*
				echo '<a style="margin-bottom: 3px;" class="btn btn-sm btn-success" href="' . $asset->browser_download_url . '">' .
					$asset->name . '&nbsp; <span class="badge">';
				echo $asset->download_count . '</span></a><br>';
				*/
			}

			if ($found) $count++;

			if ($format == "vert") {
				if ($count == 1) {
					echo '<a class="label label-success dl-vert-label" href="download.php"><span class="glyphicon glyphicon-arrow-right"></span>&nbsp;other systems</a>' . $delim;
				}
				echo '<a class="label label-info dl-horiz-label" target="_blank" href="' . $item->html_url . '"><span class="glyphicon glyphicon-ok"></span>&nbsp; release notes</a>';
			} else {
				//echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse_' . $repo . $count . '" class="collapsed">Collapsible Group Item #1</a>';
				//echo '<div id="release-notes" ><a class="label label-info" target="_blank" href="' . $item->html_url . '"><span class="glyphicon glyphicon-ok"></span>&nbsp; release notes</a></div>';
				echo '<div id="release-notes">';
				echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse_' . $repo . $count . '" class="collapsed">release notes</a>';
				echo '<div id="collapse_' . $repo . $count . '" class="collapse"><div style="display:inline-block"><pre>' . $item->body . '</pre></div></div>';
				echo '</div>';
			}

			if ($count >= $max_releases) {
				break;
			} else {
				echo '<hr>';
			}
		}
	} else {
		echo '<p class="label label-danger">Error getting feed</p>';
	}

}


/*
 * Get "32-bit", "64-bit", etc based on Download URL
 */
function get_arch($text) {
	$arch64 = array('amd64', 'win64', 'x86-64', 'x64', '64-bit', '.dmg');
	foreach ($arch64 as $x) {
		if (strpos(strtolower($text), $x) !== false) {
			return '64-bit';
		}
	}
	return '32-bit';
}

/*
 * Get "Windows", "Apple", etc based on Download URL
 */
function get_os_name($text) {
	if (strpos($text, '.tar.') !== false) {
		return 'Source Tarball';
	} else if (strpos($text, '.deb') !== false) {
			return 'Ubuntu ' . get_arch($text);
	} else if (strpos($text, '.rpm') !== false) {
			return 'Fedora ' . get_arch($text);
	} else if (strpos($text, '.dmg') !== false) {
		return 'Apple OS X';
	} else if (strpos($text, '.exe') !== false) {
			return 'Windows ' . get_arch($text);
	} else {
		return $text;;
	}
}


?>
