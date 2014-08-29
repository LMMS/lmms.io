<?php

if( isset( $_SESSION["remote_user"] ) )
{
	if( !$_POST["commentok"] ) 
	{
		$form = new form ($LSP_URL.'?comment=add&'.file_show_query_string() );
		echo '<h2>Add a comment to '.get_file_name( $_GET["file"] ).'</h2>'."\n";

		echo '<br /><b>Your comment:</b> <br /><textarea cols="50" rows="20" name="text">'."\n";
		echo "</textarea><br /><br />\n";
		echo "<b>Please not not submit offending, pornographic, racistic or violence glorifying comments!</b><br /><br />\n";
		echo '<input type="submit" name="commentok" value="Submit" />'."\n";
		$form->close();
	} 
	else
	{
		add_visitor_comment( $_GET["file"], $_POST["text"], $_SESSION["remote_user"] );
	  
		echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your comment has been saved.</span><br /><br />";
	}
}
else
{
	echo "<br /><span style=\"font-weight:bold; color:#f80;\">You need to be logged in!</span><br /><br />";
}

?>
