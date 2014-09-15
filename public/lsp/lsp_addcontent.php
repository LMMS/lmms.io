<?php

include "inc/config.inc.php";

global $TMP_DIR;
global $DATA_DIR;

echo '<div class="col-md-9"><table class="table table-striped">';
create_title('Add File');

if (!SESSION_EMPTY()) {
	if (POST_EMPTY('ok') && POST_EMPTY('addfinalok')) {
		$max_filesize = ini_get ('upload_max_filesize');
		// TODO: Make this a bootstrap form
		$form = new form($LSP_URL . '?content=add');
		echo '<input type="file" name="filename" />';
		//echo '<br />Maximum file size: '.$max_filesize.'B<br /><br />';
		echo '<br />Maximum file size: 1 MB<br /><br />';
		echo '<b>Please note that rendered songs are not desired due to database size limitations! Upload your LMMS project file instead.</b><br/><br/>';
		echo '<input type="checkbox" id="nocopyright" name="nocopyright" style="width:12px; height:12px; margin-right:10px;">';
		echo '<label for="nocopyright">I ensure that this content does not violate any existing copyright, law or trademark. Furthermore this is no offending, pornographic, racistic or violence glorifying content.</label><br/><br />';
		echo '<input type="submit" name="ok" value="OK" />';
		$form->close();
	} else if(GET('content') == "add" ) {
		if (POST_EMPTY('tmpname')) $file = $_FILES["filename"]["tmp_name"]; else $file = POST('tmpname');
		if (POST_EMPTY('fn')) $filename = $_FILES["filename"]["name"]; else $filename = POST('fn');
		if (POST_EMPTY('fsize')) $fsize = $_FILES["filename"]["size"]; else $fsize = POST('fsize');
		$nocopy = POST('nocopyright');
		$cat = POST('category');

		$ulfile = $TMP_DIR.$file;

		if (POST('ok') == 'OK') {
			if (POST_EMPTY('nocopyright')) {
				echo "You cannot upload copyrighted files on LSP !<br />\n";
				return;
			}
			$firstdot = strpos($filename, '.');
			$fext = substr( $filename, $firstdot, strlen( $filename ) - $firstdot );
			$cats = get_categories_for_ext( $fext );
			if ($cats != false) {
				if (isset($_FILES["filename"]["tmp_name"])) {
					move_uploaded_file($_FILES["filename"]["tmp_name"],	$ulfile);
//					echo "moving ".$_FILES["filename"]["tmp_name"]." to ".$ulfile;
					$form = new form( $LSP_URL.'?content=add' );
					echo '<h2>Adding ' . $filename . '</h2>';
					echo '<table style="border:none;" cellpadding="5">';
					echo '<tr><td>Category:</td><td><select name="category" />';
					echo $cats;
					echo "</select></td></tr>\n";
				 
					echo '<tr><td>License:</td><td><select name="license" />';
					echo get_licenses();
					echo "</select></td></tr></table>\n";
				 
					echo '<br />Description: <br /><textarea cols="50" rows="20" name="description">';

					echo "</textarea><br /><br />\n";
					echo '<input type="submit" name="addfinalok" value="OK" />';
					echo '<input type="hidden" name="fn" value="' . $filename . '" />';

					echo '<input type="hidden" name="tmpname" value="' . $file . '" />';
					echo '<input type="hidden" name="fsize" value="' . $fsize . '" />';
					echo '<input type="hidden" name="nocopyright" value="' . $nocopy . '" />';
					$form->close();
				}
				else {
					echo 'NO FILE';
				}
			}
			else {
				echo "<br /><span style=\"font-weight:bold; color:#f80; font-size:12pt;\">Sorry but your file-type is not supported. Please make sure to use one of the following file-types:</span> <br /><br /><ul>";
				connectdb();
				$res = mysql_query('SELECT DISTINCT extension FROM filetypes;');
				while ($object = mysql_fetch_object($res)) {
					echo '<li>'.$object->extension.'</li>';
				}
				echo '</ul><br /><img src="forward.png" alt="" style="vertical-align:middle; padding-right:5px;" /><a href="' . 
					$LSP_URL . '?content=add" style="font-weight:bold;">Try again</a>';
			}
		} elseif (POST('addfinalok') == 'OK' ) {  
			if (file_exists($ulfile)) {
				$firstdot = strpos($_POST["fn"], ".");
				$fext = substr(POST('fn'), $firstdot, strlen(POST('fn')) - $firstdot);

				$cat = explode('-', POST('category'));
				$catid = get_category_id($cat[0]);
				$subcatid = get_subcategory_id($cat[1]);
				$licenseid = get_license_id(POST('license'));

				$uid = get_user_id(SESSION());
				$fileid = 0;
				$filesum = sha1_file($ulfile);
				if (insert_file(POST('fn'), $uid, $catid, $subcatid, $licenseid, POST('description'), POST('fsize'), $filesum)) {
//					echo "rename ". $ulfile. " to ".$DATA_DIR.$fileid;
					rename($ulfile, $DATA_DIR.$fileid);
					echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your file has been added.</span> <br /><br />";
					//connectdb();
					//$req = "SELECT files.id AS fid FROM files ";
					//$req .= "WHERE files.user_id=".mysql_real_escape_string( $uid )." ";
					//$req .= "ORDER BY files.insert_date DESC LIMIT 1";
					//$result = mysql_query ($req);
					//$object = mysql_fetch_object ($result);
					// FIXME, this won't work, will have to get the $file_id manually
					show_file ($fileid, SESSION());
				} else {
					echo "Failed to commit file";
					echo mysql_error();
				}
			}
			else {
				echo "<br /><span style=\"font-weight:bold; color:#f80; font-size:12pt;\">File not found.</span> <br /><br />";
			}
		}
	}
} else {
	echo "<br /><span style=\"font-weight:bold; color:#f80;\">You need to be logged in!</span><br /><br />";
}
echo '</div>';
?>

