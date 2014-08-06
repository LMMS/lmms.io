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
				$text = $item->name . '&nbsp; (' . get_os_name($asset->name) . ')';
				
				$button_style = 'btn-success';
				// Change to warning button for prerelease
				if ($item->prerelease) {
					$button_style = 'btn-warning';
					$text = $text . '&nbsp;<span class="fa fa-exclamation-circle"></span>';
				}
				
				// If no $name_filter is provided, echo.  If $name_filter is provided, filter based on name
				if (!$name_filter || ($name_filter && (strpos($asset->name,$name_filter) !== false))) {
					echo '<a data-dl-count="' . $asset->download_count . '" style="margin-bottom: 3px;" class="btn btn-sm ' . $button_style . '" href="' . $asset->browser_download_url . '"><span class="glyphicon glyphicon-arrow-down"></span>&nbsp;' . $text . '</a>' . $delim;
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
					echo '<a class="label label-success" style="position: relative; top: -2px; margin-left: 55px;" href="download.php"><span class="glyphicon glyphicon-arrow-right"></span>&nbsp;other systems</a>' . $delim;
				}
				echo '<a class="label label-info" style="margin-left: 57px;" target="_blank" href="' . $item->html_url . '"><span class="glyphicon glyphicon-ok"></span>&nbsp; release notes</a>';
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
 * Get "Windows", "Apple", etc based on Download URL
 */
function get_os_name($text) {
	if (strpos($text, '.tar.') !== false) {
		return 'Source Tarball';
	} else if (strpos($text, '.deb') !== false) {
		if (strpos($text, 'amd64') !== false) {
			return 'Ubuntu 64-bit';
		} else {
			return 'Ubuntu 32-bit';
		}
	} else if (strpos($text, '.rpm') !== false) {
		if (strpos($text, 'amd64') !== false) {
			return 'Fedora 64-bit';
		} else {
			return 'Fedora 32-bit';
		}
	} else if (strpos($text, '.dmg') !== false) {
		return 'Apple OS X';
	} else if (strpos($text, '.exe') !== false) {
		if (strpos($text, 'win64') !== false) {
			return 'Windows 64-bit';
		} else {
			return 'Windows 32-bit';
		}
	} else {
		return $text;;
	}
}


?>
