<?php
require_once("../force-https.php");
include_once("dbo.php");
include_once('smtp_handler.php');

ini_set('session.use_trans_sid', false);
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $_SERVER["DOCUMENT_ROOT"] . '/../tmp/');
ini_set('arg_separator.output', '&amp;');

session_start();
require_once('../utils.php');
// Set $_GET[...] variables to their $_POST equivalents
set_get_post('file');
set_get_post('search');
set_get_post('category');
set_get_post('subcategory');
set_get_post('commentsearch');

require_once('header.php');
require_once('utils.php');
require_once("sidebar.php");
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
					get_results(GET('category'), GET('subcategory'), GET('sort'), GET('q', GET('search', '')), '', GET('order'), GET('commentsearch'));
					return;
					// default: // do nothing
			}
		
			// Process built-in functions
			switch ($func . ":" . GET($func)) {
				case 'comment:add' : require ("./comment_file.php"); return;
				case 'email:send' : require ("./send_email.php"); return;
				case 'email:verify' : require ("./verify_email.php"); return;
				case 'content:add' : require ("./add_file.php"); return;
				case 'content:update' : require ("./edit_file.php"); return;
				case 'content:delete' : require ("./delete_file.php"); return;
				case 'account:settings' : require("./user_settings.php"); return;
				case 'account:forget' : require("./password_recovery.php"); return;
				case 'action:show' : show_file(GET("file"), SESSION()); return;
				case 'action:register' : require ("./register.php"); return;
				case 'action:browse' :
					// Browsing by category seems is currently only supported "browse" option
					if (!GET_EMPTY('category')) {
						get_results(GET('category'), GET('subcategory'), GET('sort'), '', '', GET('order'));
						return;
					} else if(!GET_EMPTY('user')) {
						get_results(GET('category'), GET('subcategory'), GET('sort'), '', GET('user'), GET('order'));
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
include("footer.php");
?>
