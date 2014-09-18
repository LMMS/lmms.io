<?php

include "inc/config.inc.php";

global $TMP_DIR;
global $DATA_DIR;
global $LSP_URL;

if (!SESSION_EMPTY()) {
	if (POST_EMPTY('ok') && POST_EMPTY('addfinalok')) {
		display_warning('Do not submit offending, pornographic, racist or violent content.', array('<a href="">Add File</a>'));
		?>
		<div class="col-md-9"><div class="panel panel-default"><div class="panel-heading">
		<h3 class="panel-title"><span class="fa fa-upload"></span>&nbsp;Add File</h3></div>
		<div class="panel-body">
		<?php $form = new form($LSP_URL . '?content=add'); ?>
		<label for="filename">File to add</label>
		<div class="form-group">
		<span class="pull-left btn btn-default btn-file">
			<span class="fa fa-folder-open"></span>&nbsp;Select file<input type="file" name="filename" />
		</span><strong><span class="center"><pre class="text-warning" id="file-selected">No file selected</pre></span></strong>
		<small>Maximum file size: 1 MB</small>
		</div>
		<div class="form-group">
		<input type="checkbox" id="nocopyright" name="nocopyright" />
		<label for="nocopyright">&nbsp;<span class="text-warning fa fa-exclamation-circle"></span>
		Content does not violate any existing copyright, law or trademark
		</label>
		</div>
		<button type="submit" name="ok" value="OK" class="btn btn-primary"><span class="fa fa-upload"></span>&nbsp;Upload</button>
		<?php $form->close();?>
		</div></div></div><?php
	} else if(GET('content') == "add" ) {
		if (POST_EMPTY('tmpname')) $file = $_FILES["filename"]["tmp_name"]; else $file = POST('tmpname');
		if (POST_EMPTY('fn')) $filename = $_FILES["filename"]["name"]; else $filename = POST('fn');
		if (POST_EMPTY('fsize')) $fsize = $_FILES["filename"]["size"]; else $fsize = POST('fsize');
		$nocopy = POST('nocopyright');
		$cat = POST('category');

		$ulfile = $TMP_DIR.$file;

		if (POST('ok') == 'OK') {
			if (POST_EMPTY('nocopyright')) {
				display_error("Copyrighted content is forbidden", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				return;
			}
			$firstdot = strpos($filename, '.');
			$fext = substr( $filename, $firstdot, strlen( $filename ) - $firstdot );
			$categories = get_categories_for_ext( $fext );
			if ($categories != false) {
				if (isset($_FILES["filename"]["tmp_name"])) {
					move_uploaded_file($_FILES["filename"]["tmp_name"],	$ulfile);
//					echo "moving ".$_FILES["filename"]["tmp_name"]." to ".$ulfile;
					$form = new form( $LSP_URL.'?content=add' );?>
					<h2>Adding <?php echo $filename;?></h2>
					<table style="border:none;" cellpadding="5">
					<tr><td>Category:</td><td><select name="category" />
					<?php echo $categories;?>
					</select></td></tr>
					<tr><td>License:</td><td><select name="license" />
					<?php echo get_licenses(); ?>
					</select></td></tr></table>
					<br />Description: <br /><textarea cols="50" rows="20" name="description">
					</textarea><br /><br />
					<input type="submit" name="addfinalok" value="OK" />
					<input type="hidden" name="fn" value="' . $filename . '" />
					<input type="hidden" name="tmpname" value="' . $file . '" />
					<input type="hidden" name="fsize" value="' . $fsize . '" />
					<input type="hidden" name="nocopyright" value="' . $nocopy . '" /><?php
					$form->close();
				}
				else {
					echo 'NO FILE';
				}
			} else {
				display_error("Sorry, file-type <strong>$fext</strong> is not permitted", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				echo '<div class="col-md-9">';
				echo '<strong>Valid Types:</strong><ul><li>.mmpz</li><li>.mmp</li><li>etc</li></ul>';
				echo '</div>';
				/*@connectdb();
				$res = @mysql_query('SELECT DISTINCT extension FROM filetypes;');
				while ($object = @mysql_fetch_object($res)) {
					echo '<li>'.$object->extension.'</li>';
				}
				echo '</ul><br /><img src="forward.png" alt="" style="vertical-align:middle; padding-right:5px;" /><a href="' . 
					$LSP_URL . '?content=add" style="font-weight:bold;">Try again</a>';
				*/
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
	display_error("You need to be logged in!", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?action=register');
}
?>

