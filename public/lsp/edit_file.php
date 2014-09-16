<?php
require_once('utils.php');
// This line was yanked from index.php
if (get_user_id(SESSION()) == get_object_by_id("files", GET('file'), 'user_id')) {
//if( isset( $_SESSION["remote_user"] ) )
//{

	if(!POST('updateok', false)) {
		$filename = get_file_name(GET('file'));
		$firstdot = strpos($filename, '.'); 
		$fext = substr($filename, $firstdot, strlen($filename) - $firstdot);
		$cats = get_categories_for_ext($fext, get_file_category(GET('file')) . '-' . get_file_subcategory(GET('file')));
		echo '<h1>Edit ' . $filename . '</h1>';
		$form = new form($LSP_URL . '?content=update&file=' . GET('file'));
		echo '<table style="border:none;" cellpadding="5">';
		echo '<tr><td>Category:</td><td><select name="category" />';
		echo $cats;
		echo "</select></td></tr>\n";
		echo '<tr><td>License:</td><td><select name="license" />';
		echo get_licenses(get_license_name(get_file_license(GET('file'))));
		echo "</select></td></tr></table>";
			 
		echo '<br />Description: <br /><textarea cols="50" rows="20" name="description">';
		echo get_file_description(GET('file'));
		echo "</textarea><br /><br />";

		echo '<input type="submit" name="updateok" value="OK" />';
		echo '<input type="hidden" name="fn" value="'.$filename.'" />';
		echo '<input type="hidden" name="action" value="update" />';
		$form->close ();
	}  else {
		$cat = explode('-', POST('category'));
		$catid = get_category_id($cat[0]);
		$subcatid = get_subcategory_id($cat[1]);
		$licenseid = get_license_id(POST('license'));

		if (update_file(GET('file'), $catid, $subcatid, $licenseid, POST('description'))) {
			echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your changes were saved.</span> <br /><br />";
		} else {
			echo "<br /><span style=\"font-weight:bold; color:#f80;\">For some reasons your changes could not be saved. Please try again</span> <br /><br />";
		}
		show_file(GET('file'), SESSION());
	}
}
else {
	display_error('Not logged in', array('Edit', get_file_url()));
}


?>

