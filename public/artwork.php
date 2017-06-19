<?php
require_once('i18n.php');
/*
 * Creates a small summary box about an artwork item
 * Usage:
 * 	create_artwork_item($artwork_name,
 *		$img_path, $raw_path,
 *		$rendered_path,
 *		$author_name,
 *		$author_link,
 *		$modified_by);
 */
function create_artwork_item($artwork_name, $img_path, $raw_path, $rendered_path, $author_name = NULL, $author_link = NULL, $modified_by = NULL) {
	echo '<img class="art-thumb" src="' . $img_path . '">';
	echo '<h3>' . $artwork_name . '</h3>';

	if ($author_name) {
		echo '<p><strong>' . _('Author:') . '</strong> <a href="' . $author_link . '">' . $author_name . '</a><br>';
	} else {
		echo '<p>';
	}

	if ($modified_by) {
		echo '<strong>' . _('Modified/Themed By:') . ' </strong>' . $modified_by . '</p>';
	}	else {
		echo '</p>';
	}

	$buttons = array ( $raw_path, $rendered_path );

	foreach ($buttons as $button) {
		if ($button) {
			$file_name = basename($button);
			$extension = pathinfo($button)['extension'];
			$description = get_file_description($extension);
			$description = $description ? ' <br><small>(' . $description . ')</small>' : '';

			echo '<a target="_blank" class="btn btn-default" href="' . $button . '" download><span class="fa fa-image"></span> ';
			echo $file_name . $description . '</a>&nbsp; ';
		}
	}

	echo '<br><br><hr><br><br>';
}

/*
 * Basic function for retrieving a file description based on extension:
 *    i.e. "svg" = "Scalable Vector Graphics"
 * but can be moved to a common php file and/or extended to other parts of the site as well
 */
function get_file_description($extension) {
	if (!$extension) {
		return;
	}
	switch (strtolower($extension)) {
		case 'svg' : return _('Scalable Vector Format');
		case 'xcf' : return _('Gimp Image Editor Format');
		case 'pdf' : return _('Portable Document Format');
		case 'psd' : return _('Adobe Photoshop Format');
		case 'png' : return _('PNG Format');
		case 'jpg' :
		case 'jpeg': return _('JPEG Format');
		case 'bmp' : return _('Bitmap Format');
		case 'ico' : return _('Windows Icon Format');
		case 'icns': return _('Apple Icon Format');
		default:	return _("Unknown File");
	}
}
