<?php

include('github_common.php');

/*
 * Maximum number of displayed items
 */
$max=20;

/*
 * Creates an array of relational JSON objects from cached or online GitHub data
 */
$obj = @get_github_data('issues', '?state=open');

/*
 * Loop through items and echo
 */
$count = 0;

/*
 * Echo our data to the page
 */
foreach($obj as $item) {
	echo '<a target="_blank" href="' .
		$item->html_url . '"><span class="label label-info">#' . $item->number . '</span></a>&nbsp;';
	echo '<a target="_blank" href="' . $item->html_url . '">' . $item->title. '</a><br>';
	if ($count++ == $max) {
		break;
	}
}

?>
