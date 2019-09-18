<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $TMP_DIR;
global $DATA_DIR;
global $LSP_URL;

if (!SESSION_EMPTY()) {
	if (POST_EMPTY('ok') && POST_EMPTY('addfinalok')) {
		display_warning('Do not submit offending, pornographic, racist or violent content.', array('<a href="">Add File</a>'));
		echo '<div class="col-md-9">';
		$form = new form($LSP_URL . '?content=add', 'Add File', 'fa-upload'); ?>
		<label for="filename">File to add</label>
		<div class="form-group">
		<span class="pull-left btn btn-default btn-file">
			<span class="fas fa-folder-open"></span>&nbsp;Select file<input type="file" name="filename" />
		</span><strong><span class="text-center"><pre class="text-warning" id="file-selected">No file selected</pre></span></strong>
		<small>Maximum file size: 2 MB</small>
		</div>
		<div class="form-group">
		<input type="checkbox" id="nocopyright" name="nocopyright" />
		<label for="nocopyright">Does not violate any existing copyright, law or trademark</label>
		</div>
		<button type="submit" name="ok" value="OK" class="btn btn-primary"><span class="fas fa-upload"></span>&nbsp;Upload</button>
		<a href="<?php echo $LSP_URL; ?>" class="btn btn-warning"><span class="fas fa-times"></span>&nbsp;Cancel</a>
		<?php $form->close(); echo '</div>';
	} else if(GET('content') == "add" ) {
		if (POST_EMPTY('tmpname')) $tmp_path = $_FILES["filename"]["tmp_name"]; else $tmp_path = POST('tmpname');
		if (POST_EMPTY('fn')) $file_path = $_FILES["filename"]["name"]; else $file_path = POST('fn');
		if (POST_EMPTY('fsize')) $file_size = $_FILES["filename"]["size"]; else $file_size = POST('fsize');
		$no_copyright = POST('nocopyright');

		if (POST('ok') == 'OK') {
			if (POST_EMPTY('nocopyright')) {
				display_error("Copyrighted content is forbidden", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				return;
			}
			if (get_if_user_email_verified(SESSION()) == 0) {
				display_error("User email verification is required to upload file onto LSP", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				return;
			}
			$file_extension = parse_extension($file_path);
			$categories = get_categories_for_ext($file_extension);
			if ($categories != false) {
				if (isset($_FILES["filename"]["tmp_name"])) {
					echo '<div class="col-md-9">';
					create_title(array('<a href="">Add File</a>', $file_path));
					$tmp_path = $_FILES["filename"]["tmp_name"];
					$tmp_ext = trim(pathinfo($tmp_path, PATHINFO_EXTENSION));
					$tmp_name_only = pathinfo($tmp_path, PATHINFO_FILENAME) . ($tmp_ext == "" ? '' : '.' . $tmp_ext);
					move_uploaded_file($tmp_path, $TMP_DIR . $tmp_name_only);
					//echo "<code>moving $tmp_path to $TMP_DIR$tmp_name_only</code>";?>
					<?php $form = new form($LSP_URL . '?content=add', 'File Details', 'fa-upload'); ?>
					<div class="form-group">
					<label for="category">Category</label>
					<select name="category" class="form-control"><?php echo $categories;?></select>
					</div>
					<div class="form-group">
					<label for="license">License</label>
					<select name="license" class="form-control"><?php echo get_licenses();?></select>
					</div>

					<div class="form-group">
					<label for="description">Description</label>
					<textarea id="description" name="description" class="form-control"></textarea>
					</div>
					<button type="submit" class="btn btn-primary" name="addfinalok" value="Add File"><span class="fas fa-check"></span>&nbsp;Add File</button>&nbsp;
					<a href="" class="btn btn-warning"></span><span class="fas fa-times"></span>&nbsp;Cancel</a>
					<input type="hidden" name="fn" value="<?php echo $file_path; ?>" />
					<input type="hidden" name="tmpname" value="<?php echo "$TMP_DIR$tmp_name_only"; ?>" />
					<input type="hidden" name="fsize" value="<?php echo $file_size; ?>" />
					<input type="hidden" name="nocopyright" value="<?php echo $no_copyright; ?>" />
					<?php $form->close(); echo '</div>';
				} else {
					display_error("No file specified", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				}
			} else {
				display_error("Sorry, file-type <strong>$file_extension</strong> is not permitted", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				echo '<div class="col-md-9">';
				$extensions = get_extensions();
				echo "<strong>Valid Types:</strong><pre><ul>$extensions</ul></pre>";
				echo '</div>';
			}
		} elseif (POST('addfinalok') == 'Add File' ) {
			$tmp_path = POST("tmpname");
			$file_name = POST("fn");
			$file_extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
			if (file_exists($tmp_path)) {
				$category = explode(' - ', POST('category'))[0];
				$subcategory = explode(' - ', POST('category'))[1];
				$category_id = get_category_id($category);
				$subcategory_id = get_subcategory_id($category_id, $subcategory);
				$license_id = get_license_id(POST('license'));

				$user_id = get_user_id(SESSION());
				$file_id = insert_file(
					$file_name, 
					$user_id, 
					$category_id, 
					$subcategory_id, 
					$license_id, 
					POST('description'), 
					POST('fsize'), 
					sha1_file($tmp_path)
				);
				if ($file_id > 0) {
					//echo "<code>rename " . $tmp_path . " to " . $DATA_DIR . $file_id . '</code>';
					rename($tmp_path, $DATA_DIR . $file_id);
					show_file($file_id, SESSION(), "File added successfully");
				} else {
					display_error("Failed to commit file <strong>$file_extension</strong>", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
				}
			} else {
				display_error("Sorry, the uploaded file is no longer available.", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?content=add');
			}
		} else {
			display_error("Something went wrong");
		}
	}
} else {
	display_error("You need to be logged in!", array('<a href="">Add File</a>', 'Error'), $LSP_URL . '?action=register');
}
?>
