<?php

ini_set('session.use_trans_sid',false);
ini_set('session.save_handler', 'files');
ini_set('session.save_path','../../tmp');
ini_set('arg_separator.output','&amp;');

session_start ();
include_once("inc/mysql.inc.php");
include_once("inc/xhtml.inc.php");
include_once("../header.php");
require_once('lsp_utils.php');
include_once("lsp_sidebar.php");

// Set $_GET[...] variables to their $_POST equivalents
set_get_post('file');
set_get_post('search');
set_get_post('category');
set_get_post('subcategory');
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
					get_results(GET('category'), GET('subcategory'), GET('sort'), GET('q', GET('search', '')));
					return;
					// default: // do nothing
			}
		
			// Process built-in functions
			switch ($func . ":" . GET($func)) {
				case 'comment:add' : require ("./lsp_addcomment.php"); return;
				case 'content:add' : require ("./lsp_addcontent.php"); return;
				case 'content:update' : require ("./lsp_updatecontent.php"); return;
				case 'content:delete' : require ("./lsp_delcontent.php"); return;
				case 'account:settings' : require("./lsp_accountsettings.php"); return;
				case 'action:show' : show_file(GET("file"), SESSION()); return;
				case 'action:register' : require ("./lsp_adduser.php"); return;
				case 'action:browse' :
					// Browsing by category seems is currently only supported "browse" option
					if (!GET_EMPTY('category')) {
						get_results(GET('category'), GET('subcategory'), GET('sort'));	
						return;
					} else if(!GET_EMPTY('user')) {
						get_results(GET('category'), GET('subcategory'), GET('sort'), '', GET('user'));
						return;
					}
					// default: // do nothing
			}
		}
	}
	
	// All else fails, show the "Latest Uploads" page
	get_latest();
}

echo '</div>';
include("../footer.php");
?>


