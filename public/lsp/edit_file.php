<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;

if (get_user_id(SESSION()) == get_object_by_id("files", GET('file'), 'user_id') || is_admin(get_user_id(SESSION()))) {
	if(!POST('updateok', false)) {
		$file_name = get_file_name(GET('file'));
		$file_extension = parse_extension($file_name);
		$categories = get_categories_for_ext($file_extension, get_file_category(GET('file')) . ' - ' . get_file_subcategory(GET('file')));
		echo '<div class="col-md-9">';
		create_title(array('Edit', get_file_url()));
		$form = new form($LSP_URL . '?content=update&file=' . GET('file'), $title = 'Edit File', 'fa-pencil'); ?>
		<div class="form-group">
			<label for="category">Category</label>
			<select name="category" class="form-control">
			<?php echo $categories; ?>	
			</select>
		</div>
		<div class="form-group">
			<label for="license">License</label>
			<select name="license" class="form-control">
			<?php echo get_licenses(get_license_name(get_file_license(GET('file')))); ?>
			</select>
		</div>
		<div class="form-group">
			<label for="description">Description</label>
			<textarea rows=20 name="description" class="form-control"><?php
				echo htmlspecialchars_decode(get_file_description(GET('file')), ENT_COMPAT);
			?></textarea>
		</div>
		<button class="btn btn-primary" type="submit" name="updateok" value="OK"><span class="fas fa-check"></span>&nbsp;Update File</button>
		<a href="<?php echo "$LSP_URL?action=show&file=" . GET('file'); ?>" class="btn btn-warning"><span class="fas fa-close"></span>&nbsp;Cancel</a>
		<input type="hidden" name="fn" value="'.$file_name.'" />
		<input type="hidden" name="action" value="update" />
		<?php $form->close(); echo '</div>';
	}  else {
		$category = explode(' - ', POST('category'))[0];
		$subcategory = explode(' - ', POST('category'))[1];
		$category_id = get_category_id($category);
		$subcategory_id = get_subcategory_id($category_id, $subcategory);
		$license_id = get_license_id(POST('license'));

		$success = false;
		if (update_file(GET('file'), $category_id, $subcategory_id, $license_id, POST('description'))) {
			$success = true;
		}
		
		show_file(GET('file'), SESSION(), $success);
	}
}
else {
	display_error('Sorry, you cannot edit this file.', array('Edit', get_file_url()));
}


?>
