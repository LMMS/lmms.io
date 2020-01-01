<?php
require_once("../force-https.php");
include_once("dbo.php");

ini_set('session.use_trans_sid', false);
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $_SERVER["DOCUMENT_ROOT"] . '/../var/cache/lsp/');
ini_set('arg_separator.output', '&amp;');

session_start();
require_once('../utils.php');
// Set $_GET[...] variables to their $_POST equivalents
set_get_post('file');
set_get_post('search');
set_get_post('category');
set_get_post('subcategory');
set_get_post('commentsearch');

require_once('polyfill.php');
require_once('utils.php');

process_params();
/*
 * Process the URL parameters in a natural order
 * Note:  Most of these perform a function and return, but some, such as
 * file and rate perform a function and continue to remain consistent with
 * old functionality.  TODO:  Determine if the continue's are needed.
 */
function process_params() {
	$post_funcs = explode(',', POST_FUNCS);
	foreach ($post_funcs as $func) {
		if (!GET_EMPTY($func)) {
			// Process parametrized functions
			switch($func) {
				case 'rate':
					update_rating(GET('file'), GET('rate'), SESSION());
					break;  // break for file/rate, return for all others
				case 'search': //move down
				case 'q':
					$search = GET('q', GET('search', ''));
					$results = get_results(GET('category'), GET('subcategory'), GET('sort'), $search, '', GET('order'), GET('commentsearch'));
					echo twig_render('lsp/results_list.twig', [
						'rows' => $results,
						'titles' => [GET('category'), GET('subcategory'), $search ? "\"$search\"": null]
					]);
					return;
					// default: // do nothing
			}
		
			// Process built-in functions
			switch ($func . ":" . GET($func)) {
				case 'comment:add' : require ("./comment_file.php"); return;
				case 'content:add' : require ("./add_file.php"); return;
				case 'content:update' : require ("./edit_file.php"); return;
				case 'content:delete' : require ("./delete_file.php"); return;
				case 'account:settings' : require("./user_settings.php"); return;
				case 'action:show' : 
					$results = show_file(GET("file"), SESSION());
					echo twig_render('lsp/show_file.twig', [
						'titles' => [GET('category'), GET('subcategory')],
						'rows' => $results
					]);
					return;
				case 'action:register' : require ("./register.php"); return;
				case 'action:browse' :
					// Browsing by category seems is currently only supported "browse" option
					if (!GET_EMPTY('category')) {
						$results = get_results(GET('category'), GET('subcategory'), GET('sort'), '', '', GET('order'));
						echo twig_render('lsp/results_list.twig', [
							'titles' => [GET('category'), GET('subcategory')],
							'rows' => $results
						]);
						return;
					} else if(!GET_EMPTY('user')) {
						$results = get_results(GET('category'), GET('subcategory'), GET('sort'), '', GET('user'), GET('order'));
						echo twig_render('lsp/results_list.twig', [
							'titles' => '(' . GET('user') . ')',
							'rows' => $results
						]);
						return;
					}
					// default: // do nothing
			}
		}
	}
	
	// All else fails, show the "Latest Uploads" page
	echo twig_render('lsp/index.twig', [
		'rows' => get_latest()
	]);
}

echo '</div>';
?>
