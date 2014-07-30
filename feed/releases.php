<?php

include('github_common.php');


/*
 * Maximum number of displayed items
 */
$max=4;

/*
 * Creates an array of relational JSON objects from cached or online GitHub data
 */
$obj = get_github_data('releases', '');
$count = 0;

/*
 * Loop through items and echo our data to the page
 */
if ($obj) {
	foreach($obj as $item) {
		foreach($item->assets as $asset) {
			$text = $item->name . '&nbsp; (' . get_os_name($asset->name) . ')';

			echo '<a data-dl-count="' . $asset->download_count . '" style="margin-bottom: 3px;" class="btn btn-sm btn-success" href="' . $asset->browser_download_url . '"><span class="glyphicon glyphicon-arrow-down"></span>&nbsp;' . $text . '</a><br>';

			/*
			echo '<a style="margin-bottom: 3px;" class="btn btn-sm btn-success" href="' . $asset->browser_download_url . '">' .
				$asset->name . '&nbsp; <span class="badge">';
			echo $asset->download_count . '</span></a><br>';
			*/
		}
		echo '<a class="label label-info" style="margin-left: 57px;" target="new" href="download.php">other systems</a><br>';
		echo '<a class="label label-info" style="margin-left: 57px;" target="new" href="' . $item->html_url . '"><span class="glyphicon glyphicon-ok"></span>&nbsp; release notes</a>';

		if (++$count == $max) {
			break;
		} else {
			echo '<hr>';
		}
		if ($count == 1) {
			echo '<h3><span class="label label-warning"><span class="glyphicon glyphicon-hand-down"></span>&nbsp; Prev. Versions</span></h3><br><br>';
		}

	}
} else {
	echo '<p class="label label-danger">Error getting feed</p>';
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
