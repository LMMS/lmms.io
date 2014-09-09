<?php
// This line was yanked from index.php
if (get_user_id(SESSION()) == get_object_by_id("files", GET('file'), 'user_id')) {
//if( isset( $_SESSION["remote_user"] ) )
//{

	if( !$_POST["updateok"] )
	{
		$filename = get_file_name( $_GET["file"] );
		$firstdot = strpos( $filename, "." ); 
		$fext = substr( $filename, $firstdot, strlen( $filename ) - $firstdot );
		$cats = get_categories_for_ext( $fext, get_file_category( $_GET["file"] ).'-'.get_file_subcategory( $_GET["file"] ) );
		echo '<h1>Edit '.$filename.'</h1>'."\n";
		$form = new form( $LSP_URL.'?content=update&file='.$_GET["file"] );
		echo '<table style="border:none;" cellpadding="5">';
		echo '<tr><td>Category:</td><td><select name="category" />'."\n";
		echo $cats;
		echo "</select></td></tr>\n";
		echo '<tr><td>License:</td><td><select name="license" />'."\n";
		get_licenses( get_license_name( get_file_license( $_GET["file"] ) ) );
		echo "</select></td></tr></table>\n";
			 
		echo '<br />Description: <br /><textarea cols="50" rows="20" name="description">'."\n";
		echo get_file_description( $_GET["file"] );
		echo "</textarea><br /><br />\n";

		echo '<input type="submit" name="updateok" value="OK" />'."\n";
		echo '<input type="hidden" name="fn" value="'.$filename.'" />'."\n";
		echo '<input type="hidden" name="action" value="update" />'."\n"; 
		$form->close ();
	} 
	else
	{
		$cat = explode( '-', $_POST["category"] );
		$catid = get_category_id( $cat[0] );
		$subcatid = get_subcategory_id( $cat[1] );
		$licenseid = get_license_id( $_POST["license"] );

		if( update_file( $_GET["file"], $catid, $subcatid, $licenseid, $_POST["description"] ) )
		{
			echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your changes were saved.</span> <br /><br />";
		}
		else
		{
			echo "<br /><span style=\"font-weight:bold; color:#f80;\">For some reasons your changes could not be saved. Please try again</span> <br /><br />";
		}
		show_file( $_GET["file"], $_SESSION["remote_user"] );
	}
}
else
{
	echo "<br /><span style=\"font-weight:bold; color:#f80;\">You need to be logged in!</span><br /><br />";
}


?>

