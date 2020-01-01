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
		echo twig_render('lsp/edit_file.twig', [
			'titles' => array('Edit', get_file_url()),
			'categories' => $categories,
			'file_id' => GET('file'),
			'licenses' => get_licenses(get_license_name(get_file_license(GET('file')))),
			'description' => htmlspecialchars_decode(get_file_description(GET('file')), ENT_COMPAT)
		]);
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
		
		redirect($LSP_URL . '?action=show&file=' . GET('file'));
	}
}
else {
	display_error('Sorry, you cannot edit this file.', array('Edit', get_file_url()));
}


?>
