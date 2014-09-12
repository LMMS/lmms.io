<?php

include "inc/config.inc.php";

global $TMP_DIR;
global $DATA_DIR;

if( isset( $_SESSION["remote_user"] ) )
{

	if( !$_POST["ok"] && !$_POST["addfinalok"] )
	{
		$max_filesize = ini_get ('upload_max_filesize');
		echo "<h1>Add file</h1>\n";
		$form = new form ($LSP_URL.'?content=add');
		echo '<input type="file" name="filename" />'."\n";
		//echo '<br />Maximum file size: '.$max_filesize.'B<br /><br />';
		echo '<br />Maximum file size: 1 MB<br /><br />';
		echo '<b>Please note that rendered songs are not desired due to database size limitations! Upload your LMMS project file instead.</b><br/><br/>';
		echo '<input type="checkbox" id="nocopyright" name="nocopyright" style="width:12px; height:12px; margin-right:10px;">';
		echo '<label for="nocopyright">I ensure that this content does not violate any existing copyright, law or trademark. Furthermore this is no offending, pornographic, racistic or violence glorifying content.</label><br/><br />';
		echo '<input type="submit" name="ok" value="OK" />'."\n";
		$form->close();

	}
	else if($_GET["content"] == "add" )
	{
		if (!isset($_POST["tmpname"])) $file = $_FILES["filename"]["tmp_name"]; else $file = $_POST["tmpname"];
		if (!isset($_POST["fn"])) $filename = $_FILES["filename"]["name"]; else $filename = $_POST["fn"];
		if (!isset($_POST["fsize"])) $fsize = $_FILES["filename"]["size"]; else $fsize = $_POST["fsize"];
		$nocopy = $_POST["nocopyright"];
		$cat = $_POST["category"];

		$ulfile = $TMP_DIR.$file;

		if( $_POST["ok"] == 'OK' ) 
		{
			if (!isset ($_POST["nocopyright"])) 
			{
				echo "You cannot upload copyrighted files on LSP !<br />\n";
				return;
			}
			$firstdot = strpos( $filename, '.' );
			$fext = substr( $filename, $firstdot, strlen( $filename ) - $firstdot );
			$cats = get_categories_for_ext( $fext );
			if( $cats != FALSE )
			{
				if (isset( $_FILES["filename"]["tmp_name"] ) )
				{
					move_uploaded_file(
						$_FILES["filename"]["tmp_name"],
					   	$ulfile);
//					echo "moving ".$_FILES["filename"]["tmp_name"]." to ".$ulfile;
					$form = new form( $LSP_URL.'?content=add' );
					echo '<h2>Adding '.$filename.'</h2>'."\n";
					echo '<table style="border:none;" cellpadding="5">';
					echo '<tr><td>Category:</td><td><select name="category" />'."\n";
					echo $cats;
					echo "</select></td></tr>\n";
				 
					echo '<tr><td>License:</td><td><select name="license" />'."\n";
					echo get_licenses();
					echo "</select></td></tr></table>\n";
				 
					echo '<br />Description: <br /><textarea cols="50" rows="20" name="description">'."\n";

					echo "</textarea><br /><br />\n";
					echo '<input type="submit" name="addfinalok" value="OK" />'."\n";
					echo '<input type="hidden" name="fn" value="'.$filename.'" />'."\n";

					echo '<input type="hidden" name="tmpname" value="'.$file.'" />'."\n";
					echo '<input type="hidden" name="fsize" value="'.$fsize.'" />'."\n";
					echo '<input type="hidden" name="nocopyright" value="'.$nocopy.'" />'."\n";
					$form->close();
				}
				else
				{
					echo 'NO FILE';
				}
			}
			else
			{
				echo "<br /><span style=\"font-weight:bold; color:#f80; font-size:12pt;\">Sorry but your file-type is not supported. Please make sure to use one of the following file-types:</span> <br /><br /><ul>";
				connectdb();
				$res = mysql_query( 'SELECT DISTINCT extension FROM filetypes;' );
				while( $object = mysql_fetch_object( $res ) )
				{
					echo '<li>'.$object->extension.'</li>';
				}
				echo '</ul><br /><img src="forward.png" alt="" style="vertical-align:middle; padding-right:5px;" /><a href="'.$LSP_URL.'?content=add" style="font-weight:bold;">Try again</a>';
			}
		} 
		elseif( $_POST["addfinalok"] == 'OK' )
		{  
			if( file_exists( $ulfile ) )
			{
				$firstdot = strpos( $_POST["fn"], "." );
				$fext = substr( $_POST["fn"], $firstdot, strlen( $_POST["fn"] ) - $firstdot );

				$cat = explode( '-', $_POST["category"] );
				$catid = get_category_id( $cat[0] );
				$subcatid = get_subcategory_id( $cat[1] );
				$licenseid = get_license_id( $_POST["license"] );


				$uid = get_user_id( $_SESSION["remote_user"] );
				$fileid = 0;
				$filesum = sha1_file($ulfile);
				if( insert_file( $_POST["fn"], $uid, $catid, $subcatid, $licenseid, $_POST["description"], $_POST["fsize"], $filesum, &$fileid ) )
				{
//					echo "rename ". $ulfile. " to ".$DATA_DIR.$fileid;
					rename( $ulfile, $DATA_DIR.$fileid );
					echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your file has been added.</span> <br /><br />";
					//connectdb();
					//$req = "SELECT files.id AS fid FROM files ";
					//$req .= "WHERE files.user_id=".mysql_real_escape_string( $uid )." ";
					//$req .= "ORDER BY files.insert_date DESC LIMIT 1";
					//$result = mysql_query ($req);
					//$object = mysql_fetch_object ($result);
					show_file( $fileid, $_SESSION["remote_user"] );
				}
                                else
                                {
                                        echo "Failed to commit file";
					echo mysql_error();
                                }
			}
			else
			{
				echo "<br /><span style=\"font-weight:bold; color:#f80; font-size:12pt;\">File not found.</span> <br /><br />";
			}
		}

	}

}
else
{
	echo "<br /><span style=\"font-weight:bold; color:#f80;\">You need to be logged in!</span><br /><br />";
}


?>

