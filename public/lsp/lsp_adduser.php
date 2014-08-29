<?php
function add_user( $login , $pass, $pass2, $realname, $is_admin )
{
	if( $pass != $pass2 )
	{ 
		echo "password mismatch !<br />\n";
		return FALSE;
	}
	else if( $login == '' || get_user_id( $login ) > 0 )
	{
		echo "<b>The user already exists!</b><br />\n";
		return FALSE;
	}
	else
	{
		myadd_user( $login,$realname,$pass,$is_admin );
		echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your account has been created</span>";
		return TRUE;
	}
}




if( isset( $_POST["isadmin"] ) ) $isadmin = 1; else $isadmin = 0;

if( ( $_POST["adduser"] != "Create" ) ||
	( !add_user( $_POST["login"], $_POST["password"], $_POST["password2"], $_POST["realname"] ,$isadmin ) ) )
{
	echo "<h1>Create account</h1>\n";
	$form = new form( $LSP_URL."?action=register" );

	echo "<table style=\"border:none;\" cellspacing=\"5\">";
	echo "<tr><td><b>Real name:</b></td><td><input type=\"text\" name=\"realname\" class=\"textin\" /></td></tr>\n";
	echo "<tr><td><b>Login:</b></td><td><input type=\"text\" name=\"login\" /></td></tr>\n";
	echo "<tr><td><b>Password:</b></td><td><input type=\"password\" name=\"password\" /></td></tr>\n";
	echo "<tr><td><b>Confirm password:</b></td><td><input type=\"password\" name=\"password2\" /></td></tr>\n";
	//print_r ($_SERVER);
	if( myis_admin( get_user_id( $_SESSION["remote_user"] ) ) )
	{
		echo "<tr><td><b>Is administrator:</b></td><td><input type=\"checkbox\" name=\"isadmin\" /></td></tr>\n";
	}
	echo "</table><p /><input type=\"submit\" name=\"adduser\" value=\"Create\" /><p />\n";
	$form->close();
}


?>

