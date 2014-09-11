<?php

function apply_settings( $pass, $pass2, $realname )
{
	if( $pass != $pass2 )
	{ 
		echo "password mismatch !<br />\n";
		return FALSE;
	}
	else
	{
		change_user( $_SESSION["remote_user"], $realname,$pass );
		echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your account settings have been updated</span>";
		return TRUE;
	}
}




if( ( $_POST["settings"] != "apply" ) ||
	( !apply_settings( $_POST["password"], $_POST["password2"], $_POST["realname"] ) ) )
{
	echo "<h2>Change account</h2>\n";
	$form = new form( $LSP_URL."?account=settings" );
	echo "<table style=\"border:none;\" cellspacing=\"5\">";
	echo "<tr><td><b>Real name:</b></td><td><input type=\"text\" name=\"realname\" class=\"textin\" value=\"".get_user_realname($_SESSION['remote_user'] )."\" /></td></tr>\n";
	echo "<tr><td><b>Password:</b></td><td><input type=\"password\" name=\"password\" /></td></tr>\n";
	echo "<tr><td><b>Confirm password:</b></td><td><input type=\"password\" name=\"password2\" /></td></tr>\n";
	echo "</table><p /><input type=\"submit\" name=\"settings\" value=\"apply\" /><p />\n";
	$form->close();
}


?>

