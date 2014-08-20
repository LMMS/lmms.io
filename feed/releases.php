<?php

include_once('json_common.php');


/*
 * Default number of displayed items
 */
$max=1;

/*
 * Gets the past x number of releases in the specified format
 * $max_releases = 1, 2, 3, etc.
 * $name_filter = ".exe", ".dmg", ".tar.gz", etc.
 * $repo = "lmms", "diizy", "Lukas-W", "tresf", etc.
 */
function get_releases($release = NULL, $name_filter = NULL, $repo = NULL, $max_releases = NULL) {

	/*
	 * Use the default value declared above if none is specified
	 */
	if (!$max_releases) {
		global $max;
		$max_releases = $max;
	}

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
			$found = 0;
			foreach($item->assets as $asset) {
				$text = $item->name . '&nbsp;(' . get_os_name($asset->name) . ')';
				
				// Only show prerelease
				if ($release == 'prerelease' || $release == NULL) {	
					// Change to warning button for prerelease
					if ($item->prerelease) {
						$text = $text . '&nbsp;<span class="fa fa-exclamation-circle text-default"></span>';
						// make_button($asset, $button_style, $text, $icon_style, $name_filter)
						$found += make_button($asset, 'btn-warning', $text, 'download-icon', $name_filter);
					}
				} 
				// Only show stable
				else if ($release == 'stable' || $release == NULL) {
					if (!$item->prerelease) {
						// make_button($asset, $button_style, $text, $icon_style, $name_filter)
						$found += make_button($asset, 'btn-primary', $text, 'download-icon', $name_filter);
					}
				}
			}

			if ($found) {
				$count++;
				$div_id = str_replace(array('.'), '_', $asset->id . $name_filter . '_' . $count);
				echo '<div id="release-notes">';
				echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse_' . $div_id . '" class="collapsed">release notes</a>';
				echo '<div id="collapse_' .  $div_id . '" class="collapse"><div class="release-notes"><pre>' . $item->body . '</pre></div></div>';
				echo '</div><hr>';
			}
			
			if ($count >= $max_releases) {
				break;
			}
		}
	} else {
		echo '<p class="label label-danger">Error getting feed</p>';
	}

}

function make_button($asset, $button_style, $text, $icon_style, $name_filter) {
	// If no $name_filter is provided, echo.  If $name_filter is provided, filter based on name
	if (!$name_filter || ($name_filter && (strpos($asset->name,$name_filter) !== false))) {
		$title = $asset->name . " (" . number_format($asset->download_count) . ")";
		echo '<a data-dl-count="' . $asset->download_count . '" class="btn btn-lg dl-button ' . 
			$button_style . '" href="' . $asset->browser_download_url . '" title="' . $title . '"><span id="button-title">LMMS</span><br>' . 
			'<span class="fa fa-download ' . $icon_style . '"></span><big>Free Download</big><br>' .
			'<small>' . $text . '</small></a> ';
		return 1;
	}
	return 0;

	/*
	echo '<a style="margin-bottom: 3px;" class="btn btn-sm btn-success" href="' . $asset->browser_download_url . '">' .
		$asset->name . '&nbsp; <span class="badge">';
	echo $asset->download_count . '</span></a><br>';
	*/
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
