<?php
// This next line was cherry-picked from index.html. Doesn't it do the same thing as what's already here?
//if(get_user_id(SESSION()) == get_object_by_id("files", $_GET['file'], "user_id"))
if( isset( $_SESSION["remote_user"] ) &&
		( get_user_id( $_SESSION["remote_user"] ) == get_file_owner( $_GET["file"] ) ||
			is_admin( get_user_id( $_SESSION["remote_user"] ) ) ) )
{
	if( $_GET["confirmation"] == "true" )
	{
		delete_file( $_GET["file"] );
		echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your file has been deleted.</span> <br /><br />";
		get_latest();
	}
	else
	{
		echo "<br /><span style=\"font-weight:bold; font-size:12pt; color:#0a0;\">Dou really want to delete <i>".get_file_name( $_GET["file"] )."</i> with all its comments and ratings?</span> <br /><br />";
		echo '&nbsp; &nbsp;&nbsp; <b><a href="'.$LSP_URL.'?content=delete&confirmation=true&file='.$_GET["file"].'">Yes</a> &nbsp; &nbsp; &nbsp;';
		echo '<a href="'.$LSP_URL.'?action=show&file='.$_GET["file"].'">No</a></b>';
	}
}
else
{
	echo "<br /><span style=\"font-weight:bold; color:#f80; font-size:12pt;\">You're not allowed to delete this file!!!</span> <br /><br />";
	show_file( $_GET["file"], $_SESSION["remote_user"] );
}

?>

