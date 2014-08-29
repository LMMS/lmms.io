<?php

ini_set('session.use_trans_sid',false);
ini_set('session.save_handler', 'files');
ini_set('session.save_path','../../tmp');
ini_set('arg_separator.output','&amp;');

session_start ();

include ("inc/mysql.inc.php");
include ("inc/xhtml.inc.php");

// some hacks...
if( isset( $_POST["file"] ) )
{
	$_GET["file"] = $_POST["file"];
}
if( get("file") != "" &&
	( !isset( $_GET["category"] ) || !isset( $_GET["subcategory"] ) ) )
{
	$_GET["category"] = get_file_category( $_GET["file"] );
	$_GET["subcategory"] = get_file_subcategory( $_GET["file"] );
}

if( get("action") == 'getresourcesindex' )
{
	require ("lsp_resources_index.php");
	return;
}
elseif( get("action") == 'getresource' )
{
	require ("lsp_resource.php");
	return;
}



include("../header.php");
//openDocument( "LMMS Sharing Platform" );

if( $_SERVER["QUERY_STRING"] == "" && count($_POST)==0 )
{
?>
<h2>LMMS Sharing Platform</h2>

<p>Welcome to the <emph>LMMS Sharing Platform</emph> (LSP) which is a central place for LMMS users to exchange their works made with and for LMMS. This for example includes whole projects (songs) made with it or some new presets and samples. Simply browse the categories and you will surely find stuff which is interesting for you.</p>

<?php
}
if( isset( $_GET["rate"] ) && isset( $_SESSION["remote_user"] ) && $_GET["file"] != "" )
{
	update_rating( $_GET["file"], $_GET["rate"], $_SESSION["remote_user"] );
}

if( get("comment") == 'add' )
{
	require ("./lsp_addcomment.php");
}
elseif( get("content") == 'add' )
{
	require ("./lsp_addcontent.php");
}
elseif( get("content") == 'update' )
{
	connectdb();
	if( get_user_id( $_SESSION["remote_user"] ) == get_object_by_id( "files", $_GET['file'], "user_id" ) )
	{
		require ("./lsp_updatecontent.php");
	}
}
elseif( get("content") == 'delete' )
{
	connectdb();
	if( get_user_id( $_SESSION["remote_user"] ) == get_object_by_id( "files", $_GET['file'], "user_id" ) )
	{
		require ("./lsp_delcontent.php");
	}
}
elseif( get("action") == "show" )
{
	show_file( $_GET["file"], $_SESSION["remote_user"] );
}
elseif( get("account") == 'settings' )
{
	include("./lsp_accountsettings.php");
}
elseif( get("action") == 'register' )
{
	require ("./lsp_adduser.php");
}
elseif( isset($_POST['search']) || isset($_GET['q']))
{
	$q = get('q');
	if( !isset( $q ) )
	{
		$q = $_POST['search'];
	}
	echo "<h2>Search results for '".$q."':</h2>";
	echo 'Sort by';
	$sortings = array( 'date' => 'date', 'downloads' => 'downloads', 'rating' => 'rating' );
	if( !isset( $_GET['sort'] ) )
	{
		$_GET['sort'] = 'date';
	}
	foreach( $sortings as $s => $v )
	{
		echo '&nbsp;&nbsp;';
		if( get('sort') == $s )
		{
			echo '<b>'.$s.'</b>';
		}
		else
		{
			echo '<a href="'.$LSP_URL.'index.php?q='.$q.'&amp;'.rebuild_query_string( 'sort', $s ).'">'.$s.'</a>';
		}
	}
	echo '<hr />';
	get_results( $_POST['category'], $_POST['subcategory'], $_GET['sort'], mysql_real_escape_string($q));
}
else //if( !isset( $_GET["action"] ) || $_GET["action"] == "browse" )
{
	if( get("user") != "" )
	{
		show_user_content( $_GET["user"] );
	}
	elseif( get("category") == "" )
	{
		get_latest();
	}
	else
	{
		echo '<h2>'.get("category");
		if( get("subcategory") != "" )
		{
			echo '<img src="separator.png" alt="" style="vertical-align:bottom; padding-left:5px; padding-right:5px; padding-bottom:2px;" />'.$_GET["subcategory"];
		}
		echo '</h2>Sort by';
		$sortings = array( 'date' => 'date', 'downloads' => 'downloads/age', 'rating' => 'rating' );
		if( !isset( $_GET['sort'] ) )
		{
			$_GET['sort'] = 'date';
		}
		foreach( $sortings as $s => $v )
		{
			echo '&nbsp;&nbsp;';
			if( get('sort') == $s )
			{
				echo '<b>'.$v.'</b>';
			}
			else
			{
				echo '<a href="'.$LSP_URL.'index.php?'.rebuild_query_string( 'sort', $s ).'">'.$v.'</a>';
			}
		}
		echo '<hr />';
		get_results( get('category'), get('subcategory'), get('sort') );
	}
}

/*
 * Checks to see if a GET variable is set, or returns blank
 */
function get($var) {
	if (isset($_GET[$var])) {
		return $_GET[$var];
	}
	return '';
}
?>
<?php
include("../footer.php");
?>


