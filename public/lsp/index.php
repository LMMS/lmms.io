<?php

ini_set('session.use_trans_sid',false);
ini_set('session.save_handler', 'files');
ini_set('session.save_path','../../tmp');
ini_set('arg_separator.output','&amp;');

session_start ();

include_once("inc/mysql.inc.php");
include_once("inc/xhtml.inc.php");
include_once("../header.php");
include_once("lsp_sidebar.php");

// Allow HTTP posts logic to behave similar
if (!POST_EMPTY('file')) {
	$_GET["file"] = $_POST["file"];
}

if (!GET_EMPTY('file') && (GET_EMPTY('category') || GET_EMPTY('subcategory'))) {
	$_GET["category"] = get_file_category(GET('file'));
	$_GET["subcategory"] = get_file_subcategory(GET('file'));
}

if (!GET_EMPTY('rate') && !session_is_blank('remote_user') && !GET_EMPTY('file')) {
	update_rating(GET('file'), GET('rate'), SESSION('remote_user'));
}

// Process content request
if( GET("comment") == 'add' ) {
	require ("./lsp_addcomment.php");
} elseif (GET("content") == 'add' ) {
	require ("./lsp_addcontent.php");
} elseif (GET("content") == 'update') {
	connectdb();
	if (get_user_id(SESSION()) == get_object_by_id("files", GET('file'), 'user_id')) {
		require ("./lsp_updatecontent.php");
	}
} elseif (GET("content") == 'delete') {
	connectdb();
	if(get_user_id(SESSION()) == get_object_by_id("files", $_GET['file'], "user_id")) {
		require ("./lsp_delcontent.php");
	}
} elseif (GET("action") == "show") {
	show_file( GET("file"), SESSION() );
} elseif (GET("account") == 'settings') {
	include("./lsp_accountsettings.php");
} elseif( GET("action") == 'register' ) {
	require ("./lsp_adduser.php");
} elseif( !POST_EMPTY('search')  || !GET_EMPTY('q')) {
	$q = GET_EMPTY('q') ? POST('search') : GET('q');
	
	echo "<h2>Search results for '".$q."':</h2>";
	echo 'Sort by';
	$sortings = array( 'date' => 'date', 'downloads' => 'downloads', 'rating' => 'rating' );
	if (GET_EMPTY('sort')) {
		$_GET['sort'] = 'date';
	}
	
	foreach ($sortings as $s => $v) {
		echo '&nbsp;&nbsp;';
		if( GET('sort') == $s ) {
			echo '<b>'.$s.'</b>';
		} else {
			echo '<a href="'.$LSP_URL.'?q='.$q.'&amp;'.rebuild_query_string( 'sort', $s ).'">'.$s.'</a>';
		}
	}
	echo '<hr />';
	get_results( @$_POST['category'], @$_POST['subcategory'], @$_GET['sort'], mysql_real_escape_string($q));
}
else {
	if(!GET_EMPTY('user')) {
		show_user_content( $_GET['user'] );
	} elseif( GET('category') == "" ) {
		get_latest();
	} else {
		echo '<h2>' . GET('category');
		if (!GET_EMPTY('subcategory')) {
			echo '&nbsp;<span class="fa fa-caret-right lsp-caret-right"></span>&nbsp;' . GET('subcategory');
		}
		echo '</h2>';
		$sortings = array(
			'date' => '<span class="fa fa-calendar"></span>&nbsp;DATE',
			'downloads' => '<span class="fa fa-download"></span>&nbsp;DOWNLOADS',
			'rating' => '<span class="fa fa-star"></span>&nbsp;RATING' );
		if (GET_EMPTY('sort')) {
			$_GET['sort'] = 'date';
		}
		
		
		// List all sort options
		echo '<ul class="nav nav-pills lsp-sort">';
		foreach ($sortings as $s => $v) {
			echo '<li class="' . (GET('sort') == $s ? 'active' : '') . '">';
			echo '<a href="' . $LSP_URL . '?' . rebuild_query_string('sort', $s) . '">' . $v . '</a></li>';
		/*
			echo '&nbsp;&nbsp;&nbsp;';
			echo (GET('sort') == $s ? 
				'<span class="lsp-badge btn btn-primary"><b>' . $v . '</b></span>' : 
				'<a href="' . $LSP_URL . '?' . rebuild_query_string('sort', $s) . '">' . $v . '</a>');*/
		}
		echo '</ul>';
		
		get_results(GET('category'), GET('subcategory'), GET('sort'));
	}
}

/*
 * Prevent PHP warnings by first checking to see if a variable is set, or returns null
 */
function GET($var) {
	if (!GET_EMPTY($var)) {
		return $_GET[$var];
	}
	return null;
}

function SESSION($var = 'REMOTE_USER') {
	if (!SESSION_EMPTY($var)) {
		return $_SESSION[$var];
	}
	return null;
}

function POST($var) {
	if (!POST_EMPTY($var)) {
		return $_POST[$var];
	}
	return null;
}

/*
 * Check for non-blank values
 */
function GET_EMPTY($var) {
	return isset($_GET[$var]) ? trim($_GET[$var]) == '' : true;
}

function POST_EMPTY($var) {
	return isset($_POST[$var]) ? trim($_POST[$var]) == '' : true;
}

function SESSION_EMPTY($var) {
	return isset($_SESSION[$var]) ? trim($_SESSION[$var]) == '' : true;
}



?>
<?php
include("../footer.php");
?>


