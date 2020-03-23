<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $TMP_DIR;
global $DATA_DIR;
global $LSP_URL;

if (!SESSION_EMPTY()) {
	if (POST_EMPTY('ok') && POST_EMPTY('addfinalok')) {
		echo twig_render('lsp/add_file.twig', []);
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
			$file_extension = parse_extension($file_path);
			$categories = get_categories_for_ext($file_extension);
			if ($categories != false) {
				if (isset($_FILES["filename"]["tmp_name"])) {
					$tmp_path = $_FILES["filename"]["tmp_name"];
					$tmp_ext = trim(pathinfo($tmp_path, PATHINFO_EXTENSION));
					$tmp_name_only = pathinfo($tmp_path, PATHINFO_FILENAME) . ($tmp_ext == "" ? '' : '.' . $tmp_ext);
					move_uploaded_file($tmp_path, $TMP_DIR . $tmp_name_only);
					//echo "<code>moving $tmp_path to $TMP_DIR$tmp_name_only</code>";
					echo twig_render('lsp/edit_file.twig', [
						'titles' => array('<a href="">Add File</a>', $file_path),
						'categories' => $categories,
						'file_id' => GET('file'),
						'licenses' => get_licenses(),
						'fn' => $file_path,
						'tmpname' => "$TMP_DIR$tmp_name_only",
						'fsize' => $file_size,
						'nocopyright' => $no_copyright
					]);
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
			if (strpos($tmp_path,'..')!==false) {
				display_error('Invalid filename');
				return;
			}
			$tmp_path = $TMP_DIR.$tmp_path;
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
					htmlspecialchars_decode(POST('description')),
					POST('fsize'),
					sha1_file($tmp_path)
				);
				if ($file_id > 0) {
					//echo "<code>rename " . $tmp_path . " to " . $DATA_DIR . $file_id . '</code>';
					rename($tmp_path, $DATA_DIR . $file_id);
					redirect($LSP_URL . '?action=show&file=' . $file_id);
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
