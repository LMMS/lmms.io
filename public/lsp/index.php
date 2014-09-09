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

// Allow HTTP posts logic to behave similar
if (!POST_EMPTY('file')) {
	$_GET["file"] = POST('file');
}

if (!POST_EMPTY('category')) {
	$_GET['category'] = POST('category');
}

if (!POST_EMPTY('subcategory')) {
	$_GET['subcategory'] = POST('subcategory');
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
	create_title(array(GET('category'), GET('subcategory'), "\"$q\""));
	/*
	$prefix = '';
	if (!GET_EMPTY('category')) {
		$prefix .= GET('category');
		if (!GET_EMPTY('subcategory')) {
			$prefix .= '&nbsp;<span class="fa fa-caret-right lsp-caret-right"></span>&nbsp;' . GET('subcategory');
		}
		$prefix .= '&nbsp;<span class="fa fa-caret-right lsp-caret-right"></span>&nbsp;';
	}
	echo "<h3>${prefix}\"".$q."\"</h3>";*/
	list_sort_options('q=' . $q . '&');
	get_results( GET('category'), GET('subcategory'), GET('sort'), mysql_real_escape_string($q));
}
else {
	if(!GET_EMPTY('user')) {
		show_user_content( $_GET['user'] );
	} elseif( GET('category') == "" ) {
		get_latest();
	} else {
		create_title(array(GET('category'), GET('subcategory')));
		list_sort_options();
		get_results(GET('category'), GET('subcategory'), GET('sort'));
	}
}

function list_sort_options($query_prefix = '') {
	global $LSP_URL;
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
		echo '<a href="' . $LSP_URL . '?' . $query_prefix . rebuild_url_query('sort', $s) . '">' . $v . '</a></li>';
	/*
		echo '&nbsp;&nbsp;&nbsp;';
		echo (GET('sort') == $s ? 
			'<span class="lsp-badge btn btn-primary"><b>' . $v . '</b></span>' : 
			'<a href="' . $LSP_URL . '?' . rebuild_url_query('sort', $s) . '">' . $v . '</a>');
			
				/*
	echo 'Sort by';
	$sortings = array( 'date' => 'date', 'downloads' => 'downloads', 'rating' => 'rating' );
	if (GET_EMPTY('sort')) {
		$_GET['sort'] = 'date';
	}
	
	// old ?q= code
	foreach ($sortings as $s => $v) {
		echo '&nbsp;&nbsp;';
		if( GET('sort') == $s ) {
			echo '<b>'.$s.'</b>';
		} else {
			echo '<a href="'.$LSP_URL.'?q='.$q.'&amp;'.rebuild_url_query( 'sort', $s ).'">'.$s.'</a>';
		}
	}
	echo '<hr />';
	*/
	}
	echo '</ul>';
}

?>
</div>
<?php
include("../footer.php");
?>


