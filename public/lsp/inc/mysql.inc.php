<?php

$DB_TYPE = 'mysql';
$DB_HOST = 'localhost';
$DB_USER = 'someuser';
$DB_PASS = 'P@SSWORD';
$DB_DATABASE = 'somedatabase';
$DB_CHARSET = 'utf8';
$PAGE_SIZE = 25;
$MAX_LOGIN_ATTEMPTS = 6;

/*
 * When set to true will attempt to echo database statements and values to screen
 */
define('DBO_DEBUG', false);

/*
 * Tables allowed to perform actions against
 */ 
define('DBO_TABLES', 'categories,comments,files,filetypes,licenses,ratings,subcategories,users');

/*
 * Valid root functions to be looped over and processed by index.php. This order is important
 * as a user could potentially key in many functions, but we only want to process one.
 */
define('POST_FUNCS', 'comment,content,action,search,q');

/*
 * MySQL functions allowed to be called around non-specific columns
 * All function names should be lower-case
 */
define('DBO_FUNCS', 'count,avg');

require_once('config.inc.php');
require_once('lsp_utils.php');

/*
 * Returns a reference to the database object
 */
function &get_db() {
	global $DB_HOST, $DB_USER, $DB_PASS, $DB_DATABASE, $DB_CHARSET, $DB_TYPE;
	$dbh = new PDO($DB_TYPE . ':host=' . $DB_HOST . ';dbname=' . $DB_DATABASE . ';charset=' . $DB_CHARSET, $DB_USER, $DB_PASS);
	return $dbh;
}

/*
 * When DBO_DEBUG is set to true, additional query data will be echoed to screen
 */
function debug($object) {
	if (DBO_DEBUG) {
		echo '<pre>';
		print_r($object);
		echo '</pre>';
	}
}

/*
 * Checks a provided table name against a white-list of known good tables names
 */
function is_valid_table($table) {
	$valid_tables = explode(',', DBO_TABLES);
	if (array_search($table, $valid_tables) === false) {
		die('Database table "' . $table . '" is invalid in this context.');
    }
	return true;
}

/*
 * Checks a provided table name against a white-list of known good tables names
 * Blank (null) function names are perfectly valid.
 */
function is_valid_function($func) {
	if (!isset($func)) {
		return true;
	}
    $valid_func = explode(',', DBO_FUNCS);
	if (array_search(strtolower($func), $valid_func) === false) {
		die('Function "' . $func . '" is invalid in this context.');
    }
	return true;
}

/*
 * For queries that cannot be prepared
 */
function quote($string) {
	return PDO::quote($string);
}

/*
 * Basic column/field-name sanitization by removing non alpha-numeric characters from the input string
 */
function sanitize($string) {
	return preg_replace('/[^A-Za-z0-9_]+/', '', $string);
}

function get_object_by_id($table, $id, $field, $id_field = 'id', $func = null) {
	// Sanitize column and table values
	$table = sanitize($table);
	$field = sanitize($field);
	$id_field = sanitize($id_field);
	
	// Validate the table name from a white-list
	if (is_valid_table($table) && is_valid_function($func))  {
		// If specified, wrap field with a white-listed function call
		if (isset($func)) {
			$func = strtoupper($func);
			$field = "$func($field)";
		}
		$dbh = &get_db();
		$stmt = $dbh->prepare("SELECT $field FROM $table WHERE $id_field=:id");
		debug("SELECT $field FROM $table WHERE $id_field='$id'");
		$stmt->bindParam(':id', $id);
		$object = null;
		if ($stmt->execute()) {
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$object = $object[$field];
				debug($object);
				break;
			}
		}
		$stmt = null;
		$dbh = null;
		return $object;
	}
}

/*
 * Returns a single id of a database object after searching for said object by name
 */
function get_id_by_object($table, $field, $object) {
	// Sanitize column and table values
	$table = sanitize($table);
	$field = sanitize($field);
	$object = "%{$object}%";
	
	// Validate the table name from a white-list
	if (is_valid_table($table))  {
		$dbh = &get_db();
		$stmt = $dbh->prepare("SELECT id FROM $table WHERE $field LIKE :object");
		debug("SELECT id FROM $table WHERE $field LIKE '$object'");
		$stmt->bindParam(':object', $object);
		$id = null;
		if ($stmt->execute()) {
			while ($id = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$id = $id['id'];
				break;
			}
		}
		$stmt = null;
		$dbh = null;
		debug("> id=\"$id\"");
		return $id;
	}
}

/*
 * Rebuilds the current URL into a new URL to be used in a link
 * replacing the specified key with a new key.
 */
function rebuild_url_query($key, $value) {
	if (GET_EMPTY($key)) {
		return '';
	}
	$old = GET($key);
	$_GET[$key] = $value;
	$new_query = array();
	foreach($_GET as $k => $v) {
		array_push($new_query, $k . "=" . $v);
	}
	$_GET[$key] = $old;
	return implode("&amp;", $new_query);
}

function file_show_query_string() {
	return 'action=show&file=' . GET("file");
}


function get_latest() {
	global $PAGE_SIZE;
	$dbh = &get_db();
	$stmt = $dbh->prepare('
		SELECT files.id, licenses.name AS license,size,realname,filename,users.login,
		categories.name AS category,subcategories.name AS subcategory,
		insert_date,update_date,description,files.downloads AS downloads FROM files 
		INNER JOIN categories ON categories.id=files.category 
		INNER JOIN subcategories ON subcategories.id=files.subcategory 
		INNER JOIN users ON users.id=files.user_id 
		INNER JOIN licenses ON licenses.id=files.license_id 
	 	ORDER BY files.update_date DESC LIMIT ' . sanitize($PAGE_SIZE));
		$object = null;
		if ($stmt->execute()) {
			echo create_title('Latest Uploads');
			echo '<div class="col-sm-9"><table class="table table-striped">';
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				show_basic_file_info($object, true);
				debug($object);
			}
			echo '</table></div>';
		}
		$stmt = null;
		$dbh = null;
		return $object;
}

/*
 * Attempts to match a password against a given account
 * so as long as the login attempts are less than $MAX_LOGIN_ATTEMPTS
 */
function password_match($pass, $user) {
	global $MAX_LOGIN_ATTEMPTS;
	$dbh = &get_db();
	$stmt = $dbh->prepare('SELECT login FROM users WHERE LOWER(password) = LOWER(SHA1(:pass)) AND LOWER(login) = LOWER(:user) AND loginFailureCount < :max_login_attempts');
	debug("SELECT login FROM users WHERE LOWER(password) = LOWER(SHA1($pass)) AND LOWER(login) = LOWER($user) AND loginFailureCount < $MAX_LOGIN_ATTEMPTS");
		$stmt->bindParam(':pass', $pass);
		$stmt->bindParam(':user', $user);
		$stmt->bindParam(':max_login_attempts', $MAX_LOGIN_ATTEMPTS);
		$return_val = false;
		if ($stmt->execute()) {
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if ($object['login']) {
					// Reset to "0" failed logins
					set_failure_count($user);
					$return_val = true;
				} else {
					// Increment failed logins
					set_failure_count($user, true);
				}
				break;
			}
		}
		$stmt = null;
		$dbh = null;
		return return_val;
}

/*
 * Increments or resets the login-failure count for the specified user
 */
function set_failure_count($user, $incriment=false) {
	$dbh = &get_db();
	if ($incriment) {
		$stmt = $dbh->prepare('UPDATE users SET loginFailureCount = loginFailureCount + 1 WHERE LOWER(login) = LOWER(:user)');
		debug("UPDATE users SET loginFailureCount = loginFailureCount + 1 WHERE LOWER(login) = LOWER($user)");
	} else {
		$stmt = $dbh->prepare('UPDATE users SET loginFailureCount=0 WHERE LOWER(login) = LOWER(:user)');
		debug("UPDATE users SET loginFailureCount=0 WHERE login = $user");
	}
	
	$stmt->bindParam(':user', $user);
	$stmt->execute();
	$stmt = null;
	$dbh = null;
}

/*
 * Formats today's date
 */
function mydate() {
 	return date("Y-m-d", time());
}

/*
 * Returns whether or not the specified user-id is an administrator
 */
function myis_admin($uid) {
	return get_object_by_id("users", $uid, "is_admin");
}

function myadd_user($login, $realname, $pass, $is_admin) {
	$dbh = &get_db();
	$stmt = $dbh->prepare('INSERT INTO users(login, realname, password, is_admin) VALUES(:login, :realname, SHA1(:password), :is_admin)');
	debug("INSERT INTO users(login, realname, password, is_admin) VALUES($login, $realname, SHA1($pass), $is_admin)");
	$stmt->bindParam(':login', $login);
	$stmt->bindParam(':realname', $realname);
	$stmt->bindParam(':password', $pass);
	$stmt->bindParam(':is_admin', $is_admin);
	$stmt->execute();
	$stmt = null;
	$dbh = null;
}

 /*
  * Update the realname and/or password of the specified user
  */
 function mychange_user($login, $realname, $pass = '') {
	$dbh = &get_db();
	if ($pass != '') {
		$stmt = $dbh->prepare('UPDATE users SET realname=:realname, password=SHA1(:password) WHERE LOWER(login)=LOWER(:login)');
		debug("UPDATE users SET realname=$realname, password=SHA1($password) WHERE LOWER(login)=LOWER($login)");
		$stmt->bindParam(':realname', $realname);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':login', $login);
	} else {
		$stmt = $dbh->prepare('UPDATE users SET realname=:realname WHERE LOWER(login)=LOWER(:login)');
		debug("UPDATE users SET realname=$realname WHERE LOWER(login)=LOWER($login)");
		$stmt->bindParam(':realname', $realname);
		$stmt->bindParam(':login', $login);
	}
	$stmt->execute();
	$stmt = null;
	$dbh = null;
 }

/*
 * Convenience Functions
 */
function get_user_id($login) { return get_id_by_object( "users", "login", $login ); }
function get_user_realname( $login ) { return get_object_by_id( "users", get_user_id($login), 'realname' ); }
function get_file_name($file_id){ return(get_object_by_id("files", $file_id, "filename")); }
function get_file_owner($file_id) {	return(get_object_by_id("files", $file_id, "user_id")); }
function get_file_description($file_id) { return(get_object_by_id("files", $file_id, "description")); }
function get_file_license($file_id) { return( get_object_by_id("files", $file_id, "license_id")); }
function get_comment_count( $file_id ) { return( get_object_by_id( "comments", $file_id, "1", "file_id", "count" ) ); }
function get_category_id($cat) { return(get_id_by_object("categories", "name", $cat)); }
function get_subcategory_id($cat) { return(get_id_by_object("subcategories", "name", $cat)); }



 
function get_categories()
{
	global $LSP_URL;
	connectdb();
	$result = mysql_query(
		'SELECT categories.name AS name, COUNT(files.id) AS cnt FROM categories '.
		'LEFT JOIN files ON files.category = categories.id '.
		'GROUP BY categories.name '.
		'ORDER BY categories.name ');
	debug('SELECT categories.name AS name, COUNT(files.id) AS cnt FROM categories '.
		'LEFT JOIN files ON files.category = categories.id '.
		'GROUP BY categories.name '.
		'ORDER BY categories.name ');
echo mysql_error();
	echo '<ul class="navbar lsp-categories">';
	$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
	while( $object = mysql_fetch_object( $result ) )
	{
		echo "<li class='lsp-category'><a class='category' href='".htmlentities ($LSP_URL."?action=browse&category=".$object->name)."&sort=".$sort."'>".
			$object->name." <span class='count'>(".$object->cnt.")</span></a></li>";
		if( isset( $_GET["category"] ) && $_GET["category"] == $object->name )
		{
			$cat = $_GET["category"];
			$catid = get_category_id( $object->name );
//			$res2 = mysql_query( "SELECT name FROM subcategories WHERE category='".$catid."'" );
			$res2 = mysql_query( 
				"SELECT subcategories.name AS name, COUNT(files.id) AS cnt FROM subcategories ".
				"LEFT JOIN files ON files.subcategory = subcategories.id AND files.category='$catid' ".
				"WHERE subcategories.category='$catid' ". 
				"GROUP BY subcategories.name ".
				"ORDER BY subcategories.name ");
			debug("SELECT subcategories.name AS name, COUNT(files.id) AS cnt FROM subcategories ".
				"LEFT JOIN files ON files.subcategory = subcategories.id AND files.category='$catid' ".
				"WHERE subcategories.category='$catid' ". 
				"GROUP BY subcategories.name ".
				"ORDER BY subcategories.name ");
			echo "<div class='selected'>";
	echo mysql_error();
			echo '<ul class="lsp-subcategory">';
			while( $object2 = mysql_fetch_object( $res2 ) )
			{
				echo "<li class='lsp-subcategory'><a class='subcategory";
                                if( $object2->name == @$_GET["subcategory"] )
                                {
                                        echo " selected";
                                }
				echo "' href=\"".htmlentities ($LSP_URL."?action=browse&category=$cat&subcategory=".$object2->name."&sort=".$sort)."\"> ";
				echo $object2->name." <span class='count'>(".$object2->cnt.")</span></a></li>";
			}
			mysql_free_result( $res2 );
			echo "</ul></div>";
		}
	}
	mysql_free_result( $result );
	echo '</ul>';
}




function get_categories_for_ext( $ext, $default = "" )
{
	$cats = '';
	connectdb();
	$result = mysql_query( 'SELECT categories.name AS catname, subcategories.name AS subcatname FROM filetypes INNER JOIN categories ON categories.id=filetypes.category INNER JOIN subcategories ON subcategories.category=categories.id WHERE extension LIKE \''.mysql_real_escape_string( $ext ).'\' ORDER BY categories.name, subcategories.name' );
	if( mysql_num_rows( $result ) > 0 )
	{ 
		while( $object = mysql_fetch_object( $result ) )
		{
			$fullname = $object->catname.'-'.$object->subcatname;
			if( $fullname == $default )
			{
				$def = ' selected';
			}
			else
			{
				$def = '';
			}
			$cats .= '<option'.$def.'>'.$fullname.'</option>'."\n";
		}
		mysql_free_result( $result );
		return( $cats );
	}
	return( FALSE );
}



function get_license_id( $license )
{
	return( get_id_by_object( "licenses", "name", $license ) );
}

function get_license_name( $lid )
{
	return( get_object_by_id( "licenses", $lid, "name" ) );
}


function get_licenses( $default = "" )
{
	connectdb();
	$result = mysql_query( 'SELECT name FROM licenses' );

	while( $object = mysql_fetch_object( $result ) )
	{
		if( $object->name == $default )
		{
			$def = ' selected';
		}
		else
		{
			$def = '';
		}
		echo '<option'.$def.'>'.$object->name.'</option>'."\n";
	}
	mysql_free_result( $result );
}






function get_comments($fid, $f) {
	global $LSP_URL;
	connectdb();
	$q = sprintf( "SELECT users.realname,users.login,date,text FROM comments INNER JOIN users ON users.id=comments.user_id WHERE file_id='%s' ORDER BY date", $fid );
	$result = mysql_query( $q );
	$out = '';
 	while ($object = mysql_fetch_object($result)) {
		$strong = $object->login == $f->login;
		$name = '<a href="' . $LSP_URL . '?action=browse&amp;user=' . $object->login . '">' . $object->login . '</a>';
  		$out .= '<tr><td colspan="2">';
		// Bold the comment if it's from the author
		$out .= ($strong ? '<strong>' : '');
		$out .= '<blockquote>'.htmlspecialchars($object->text, ENT_COMPAT, 'UTF-8')."";
		$out .= ($strong ? '</strong>' : '');
		$out .= '<small class="lsp-small">Posted by: ' . $name . ' on ' . $object->date . '</small></blockquote></tr></td>';
 	}
	echo (strlen($out) ? $out : '<tr><td colspan="2"><p class="text-muted">No comments yet</p></td></tr>');
	mysql_free_result( $result );
}



function get_file_category($fid) {
 	connectdb();
 	$q = sprintf( "SELECT categories.name FROM files INNER JOIN categories ON categories.id=files.category WHERE files.id='%s'", mysql_real_escape_string( $fid ) );
 	$result = mysql_query( $q );
 	$object = mysql_fetch_object( $result );
 	mysql_free_result( $result );
 	return $object->name;
}

function get_file_subcategory($fid) {
 	connectdb();
 	$q = sprintf( "SELECT subcategories.name FROM files INNER JOIN subcategories ON subcategories.id=files.subcategory WHERE files.id='%s'", mysql_real_escape_string( $fid ) );
 	$result = mysql_query( $q );
 	$object = mysql_fetch_object( $result );
 	mysql_free_result( $result );
 	return $object->name;
}


function get_results( $cat, $subcat, $sort = '', $search = '' )
{
	global $PAGE_SIZE;
	global $LSP_URL;
	$page = @$_GET["page"];
	$where = '';
	connectdb();

	if(strlen( $cat ) > 0 )
	{	
		# Where clause for count and query
		$where= sprintf( "WHERE categories.name='%s' ", mysql_real_escape_string( $cat ) );
		if( strlen( $subcat ) > 0 )
		{
			$where .= sprintf( "AND subcategories.name='%s' ", mysql_real_escape_string( $subcat ) );
		}
	}
	if( strlen($search) > 0 )
	{
		if( strlen($where) == 0 )
		{
			$where = "WHERE files.filename = files.filename ";
		}
		$where .= "AND ( files.filename LIKE '%$search%' OR users.login LIKE '%$search%' OR users.realname LIKE '%$search%') ";
	}

	# Get count
	$count = mysql_result(mysql_query(
		"SELECT COUNT(files.id) FROM files ".
		"INNER JOIN categories ON categories.id=files.category ".
		"INNER JOIN subcategories ON subcategories.id=files.subcategory ".
		"INNER JOIN users ON users.id=files.user_id ".
		$where), 0, 0);

	if( $count > 0 ) {
		$req = "SELECT files.id, licenses.name AS license,size,realname,filename,users.login,categories.name AS category,subcategories.name AS subcategory,";
		$req .= "files.downloads*files.downloads/(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(files.insert_date)) AS downloads_per_day,";
		$req .= "files.downloads AS downloads,";
		$req .= "insert_date,update_date,description,AVG(ratings.stars) as rating FROM files ";
		$req .= "INNER JOIN categories ON categories.id=files.category ";
		$req .= "INNER JOIN subcategories ON subcategories.id=files.subcategory ";
		$req .= "INNER JOIN users ON users.id=files.user_id ";
		$req .= "INNER JOIN licenses ON licenses.id=files.license_id ";
		$req .= "LEFT JOIN ratings ON ratings.file_id=files.id ";
		$req .= $where;
		$req .= "GROUP BY files.id ";
		if( $sort == 'downloads' )
		{
			$req .= "ORDER BY downloads_per_day DESC ";
		}
		else if( $sort == 'rating' )
		{
			$req .= "ORDER BY rating DESC,COUNT(ratings.file_id) DESC ";
		}
		else
		{
			$req .= "ORDER BY files.insert_date DESC ";
		}
		$req .= sprintf("LIMIT %d,%d", $page*$PAGE_SIZE, $PAGE_SIZE);
		$result = mysql_query ($req);

		echo '<br /><div class="lsp-table"><table class="table table-striped">';
		while($object = mysql_fetch_object ($result)) {
			show_basic_file_info_old( $object, TRUE );
		}
		echo'</table></div>';

		echo '<div class="lsp-pagination center"><ul class="pagination pagination-sm">';
		$pages = $count / $PAGE_SIZE;
		if ($pages > 1) {
			for($j=0; $j < $count / $PAGE_SIZE; ++$j ) {
				$class = $j==$page ? 'active' : '';
				echo '<li class="' . $class . '"><a href=' . $LSP_URL . "?action=browse&amp;category=$cat&amp;subcategory=$subcat&amp;page=$j&amp;sort=$sort>" . ($j+1) . '</a></li>';
			}
		}
		echo '</ul></div>';
		mysql_free_result( $result );
	}
	else {
		echo '<h3 class="text-muted">No results.</h3>';
	}
}


function show_user_content( $user ) {
	$uid = get_user_id( $user );
	if( $uid >= 0 ) {
		connectdb ();
		$req = "SELECT files.id, licenses.name AS license,size,realname,filename,users.login,categories.name AS category,subcategories.name AS subcategory,";
		$req .= "insert_date,update_date,description FROM files ";
		$req .= "INNER JOIN categories ON categories.id=files.category ";
		$req .= "INNER JOIN subcategories ON subcategories.id=files.subcategory ";
		$req .= "INNER JOIN users ON users.id=files.user_id ";
		$req .= "INNER JOIN licenses ON licenses.id=files.license_id ";
		$req .= "WHERE files.user_id='".mysql_real_escape_string( $uid )."' ";
		$req .= "ORDER BY files.insert_date DESC";
		$result = mysql_query ($req);

		create_title("($user)");
		
		if( $result != FALSE && mysql_num_rows( $result ) > 0 ) {	
			echo '<div class="lsp-table"><table class="table table-striped">';
			while( $object = mysql_fetch_object( $result ) )
			{
				show_basic_file_info_old( $object, TRUE, FALSE );
			}
			echo'</table></div>';
			mysql_free_result ($result);
		}
		else {
			echo '<h3 class="text-muted">No results.</h3>';
		}
	} else {
		echo '<h3 class="txt-danger">User "'.$user.'" not found!</h3>';
	}
}


function insert_category ($fext,$cat)
 {
  connectdb ();
  $req = "SELECT count(name) FROM categories WHERE name LIKE '".$cat."' AND filetypes_extension LIKE '".$fext."'";
  $result = mysql_query ($req);
  $row = mysql_fetch_row ($result);
  if (!$row[0])
   {
  mysql_free_result ($result);
  $req = "INSERT INTO categories (name,filetypes_extension) VALUES ('".$cat."','".$fext."')";
  return mysql_query ($req);
   } else return 0;
  }


function show_basic_file_info($rs, $browsing_mode = false, $show_author = true) {
	global $LSP_URL;
	$sort = GET_EMPTY('sort') ? 'date' : GET('sort');
	echo '<tr class="file"><td><div class="overflow-hidden">';
	
	if ($browsing_mode) {
		echo '<div><a href="' . htmlentities($LSP_URL . '?action=show&file=' . $rs['id']) . '" style="font-weight:bold; font-size:1.15em" title="' . $rs['filename'] . '">' . $rs['filename'] . '</a></div>';
		echo '<a href="' . htmlentities($LSP_URL . '?action=browse&category=' . $rs['category']) . '">' . $rs['category'] . '</a>&nbsp;<span class="fa fa-caret-right lsp-caret-right-small"></span>&nbsp;<a href="' . htmlentities($LSP_URL . '?action=browse&category=' . $rs['category'] . '&subcategory=' . $rs['subcategory']) . '&sort=' . $sort . '">' . $rs['subcategory'] . '</a><br>';
	}
	
	if ($show_author) {
		echo '<small>by <a href="' . $LSP_URL . '?action=browse&amp;user=' . $rs['login'] . '">' . $rs['realname'] . " (" . $rs['login'] . ")</a></small><br>";
	}

	if(!$browsing_mode) {
		$hr_size = round($rs['size']/1024) . " KB";
		echo "<b>Size:</b>&nbsp;$hr_size<br>";
		echo "<b>License:</b>&nbsp;$rs[license]<br>";
	}
	echo "</div></td><td class=\"lsp-file-info\"><small>";
	if($browsing_mode) {
		echo "<b>Date:</b>&nbsp;$rs[update_date]<br>";
	} else {
		echo "<div><b>Submitted:</b>&nbsp;$rs[insert_date]</div>";
		echo "<b>Updated:</b>&nbsp;$rs[update_date]<br>";
	}
	// TODO FIXME why is this zero when searching by user?
	$downloads = isset($rs['downloads']) ? $rs['downloads'] : 0;
	echo "<b>Popularity: </b><span class=\"lsp-badge badge\"><span class=\"fa fa-download\"></span>&nbsp;" . $downloads . "</span> ";
	echo "<span class=\"lsp-badge badge\"><span class=\"fa fa-comments\"></span>&nbsp;" . get_comment_count($rs['id']) . "</span><br>";
	echo "<b>Rating:</b> ";

	$rating = get_file_rating($rs['id']);
	for ($i = 1; $i <= $rating ; ++$i) {
		echo '<span class="fa fa-star lsp-star"></span>';
	}
	for ($i = $rating+1; floor( $i )<=5 ; ++$i) {
		echo '<span class="fa fa-star-o lsp-star-o"></span>';
	}
	//echo ' ('.round(20*$rating).'%,'; // Percentage is quite redundant
	echo '&nbsp;<span class="lsp-badge badge"><span class="fa fa-check-square-o"></span>&nbsp;'.get_file_rating_count($rs['id']).'</span>';
	//echo "<br><b>Downloads:</b> ".$rs->downloads."<br />\n";
	echo '</small></td></tr>';
}

/*
 * Temporary function to wrap old mysql object into a new associative array
 * This should go away when the mysql_connect calls have all been removed
 */
function show_basic_file_info_old($rs, $browsing_mode = false, $show_author = true) {
	$wrapper = array();
	$wrapper['id'] = $rs->id;
	//$wrapper['name'] = $rs->name;
	$wrapper['size'] = $rs->size;
	$wrapper['login'] = $rs->login;
	$wrapper['filename'] = $rs->filename;
	$wrapper['realname'] = $rs->realname;
	$wrapper['license'] = $rs->license;
	$wrapper['insert_date'] = $rs->insert_date;
	$wrapper['category'] = $rs->category;
	$wrapper['subcategory'] = $rs->subcategory;
	$wrapper['insert_date'] = $rs->insert_date;
	$wrapper['update_date'] = $rs->update_date;
	$wrapper['downloads'] = isset($rs->downloads) ? $rs->downloads : 0;
	return show_basic_file_info($wrapper, $browsing_mode, $show_author);
}



function show_file( $fid, $user )
{
	global $LSP_URL;
	connectdb();
	$req = "SELECT licenses.name AS license,size,realname,filename,users.login,categories.name AS category,subcategories.name AS subcategory,";
	$req .= "insert_date,update_date,description,downloads,files.id FROM files ";
	$req .= "INNER JOIN categories ON categories.id=files.category ";
	$req .= "INNER JOIN subcategories ON subcategories.id=files.subcategory ";
	$req .= "INNER JOIN users ON users.id=files.user_id ";
	$req .= "INNER JOIN licenses ON licenses.id=files.license_id ";
	$req .= sprintf( "WHERE files.id=%s", mysql_real_escape_string( $fid ) );

	$res = mysql_query( $req );

	if( mysql_num_rows( $res ) < 1 )
	{
		echo '<h3 style="color:#f60;">File not found</h3>';
		return;
	}
	$f = mysql_fetch_object( $res );

	$img = '&nbsp;<span class="fa fa-caret-right lsp-caret-right"></span>&nbsp;';
	echo '<h3>'.$f->category.$img.$f->subcategory.$img.$f->filename.'</h3>'."\n";
	echo '<div class="lsp-table">';
	echo '<table class="table table-striped">';
	show_basic_file_info_old( $f, FALSE );
	
	// Bump the download button under details block
	echo '<tr><td><strong>Name:</strong>&nbsp;' . $f->filename . '</td><td class="lsp-file-info">';
	$url = htmlentities( 'lsp_dl.php?file='.$f->id.'&name='.$f->filename );
	echo '<a href="'.$url.'" id="downloadbtn" class="lsp-dl-btn btn btn-primary"><span class="fa fa-download lsp-download"></span>&nbsp;Download</a>';
	echo '</td></tr>';
	
	echo "<tr><td colspan=\"2\"><strong>Description:</strong><p>";
	echo ($f->description != '' ? newline_to_br($f->description) : 'No description available.');
	echo '</p></td></tr>';
	
	echo '<tr><td colspan="2">';
	echo '<nav class="navbar navbar-default"><ul class="nav navbar-nav">';
	$can_edit = ($f->login == $user || myis_admin(get_user_id($user)));
	$can_rate = isset($_SESSION["remote_user"]);
	
	create_toolbar_item('Comment', "$LSP_URL?comment=add&file=$fid", 'fa-comment', $can_rate);
	create_toolbar_item('Edit', "$LSP_URL?content=update&file=$fid", 'fa-pencil', $can_edit);
	create_toolbar_item('Delete', "$LSP_URL?content=delete&file=$fid", 'fa-trash', $can_edit);
	$star_url = $LSP_URL . '?' . file_show_query_string().'&rate=';
	create_toolbar_item(get_stars($fid, $star_url, 'fa-star-o lsp-star', $can_rate), '', null, false);
	
	echo '</ul></nav>';
	echo '<strong>Comments:</strong>';
	echo '</td></tr>';
	get_comments($fid, $f);
	echo'</table></div>';
	
	mysql_free_result ($res);	

	
	/*
	if (isset($_SESSION["remote_user"])) {
		$urating = get_user_rating( $fid, $_SESSION["remote_user"] );
		echo'<b>Rating:</b>';
		for( $i = 1; $i < 6; ++$i )
		{
			echo '<a href="'.htmlentities($LSP_URL.'?'.file_show_query_string().'&rate='.$i ).'" class="ratelink" ';
			if( $urating == $i )
			{
				echo 'style="border:1px solid #88f;"';
			}
			echo '>';
			for( $j = 1; $j <= $i ; ++$j )
			{
				echo '<span class="fa fa-star lsp-star">';
			}
			echo '</a><br />';
		}
	}
	echo'</ul></nav></td></tr></table></div>';
	echo "<br />\n";
	*/


}

function create_toolbar_item($text, $href = '#', $font_awesome = '', $enabled = true) {
	$href = $enabled ? htmlentities($href) : '#';
	$tooltip = $enabled ? '' : 'Login to ' . strtolower(sanitize(remove_after_lt($text)));
	$font_awesome = $font_awesome == '' ? '' : 'fa ' . $font_awesome;
	echo '<li class="' . ($enabled ? '' : 'disabled') . '"><a class="pull-left" href="' . $href . '" title="' . $tooltip . '"><span class="' . $font_awesome . '"></span>&nbsp;' . $text . '</a></li>';
}

function get_stars($fid = -1, $href = '#', $font_awesome = '', $enabled = true) {
	$ret_val = 'Rate:' . ($enabled ? '' : '&nbsp; &nbsp;');
	$urating =  SESSION_EMPTY() ? get_user_rating($fid, SESSION()) : -1;
	$font_awesome = ($font_awesome = '' ? '' : 'fa ' . $font_awesome);
	$title = $enabled ? '' : 'Login to rate';
	$href = $enabled ? htmlentities($href) : '#';
	for( $i = 1; $i < 6; ++$i ) {
		$ret_val .= ($enabled ? '<a href="' . ($href == '#' ? '#' : $href . $i) . '" class="clearfix pull-left lsp-ratelink" ' : '<span class="lsp-ratelink" ');
		$ret_val .=  'title="' . $title . '">';	
		$ret_val .= '<span class="' . ($urating == $i ? 'text-primary ' : '') . $font_awesome . '"></span>';
		$ret_val .= ($enabled ? '</a>' : '</span>');
	}
	return $ret_val;
}



function get_user_rating( $fid, $user ) {
	$uid = get_user_id($user);
	if( $uid >= 0 )
	{
		connectdb ();
		$q = sprintf( "SELECT COUNT(stars) AS cnt FROM ratings WHERE `file_id`='%s' AND `user_id`='%s'", mysql_real_escape_string( $fid ), mysql_real_escape_string( $uid ) );
		$result = mysql_query( $q );
		$object = mysql_fetch_object ($result);
		mysql_free_result ($result);
		if( $object->cnt < 1 )
		{
			return( 0 );
		}

		
		$q = sprintf( "SELECT stars FROM ratings WHERE `file_id`='%s' AND `user_id`='%s'", mysql_real_escape_string( $fid ), mysql_real_escape_string( $uid) );
		$result = mysql_query( $q );

		$object = mysql_fetch_object ($result);
		mysql_free_result ($result);
		return $object->stars;
	}
}

function update_rating( $fid, $stars, $user )
{
	// check stars and user here
	if ( !isset($stars) || trim($stars) == '' ) {
		echo 'invalid rating';
		return;
	}
	
	if ( !isset($user) || trim($user) == '') {
		echo 'invalid user';
		return;
	}
	
	if( $stars < 1 || $stars > 5 )
	{
		echo "invalid";
		return;
	}
	$uid = get_user_id($user);
	if( $uid >= 0 )
	{
		if( get_user_rating( $fid, $user ) > 0 )
		{
	 		$req = sprintf( "UPDATE ratings SET `stars`='%s' WHERE `file_id`='%s' AND `user_id`='%s'",
						mysql_real_escape_string( $stars ),
						mysql_real_escape_string( $fid ),
						mysql_real_escape_string( $uid ) );
		}
		else
		{
	 		$req = sprintf( "INSERT INTO ratings(file_id,user_id,stars) VALUES('%s', '%s', '%s' )",
						mysql_real_escape_string( $fid ),
						mysql_real_escape_string( $uid ),
						mysql_real_escape_string( $stars ) );
		}
	 	connectdb();
	 	mysql_query ($req);
	}
}


function get_file_rating_count( $fid )
{
	return( get_object_by_id( "ratings", $fid, "1", "file_id", "count" ) );
}


function get_file_rating( $fid )
{
	return( get_object_by_id( "ratings", $fid, "stars", "file_id", "avg" ) );
}


function insert_file( $filename, $uid, $catid, $subcatid, $licenseid, $description, $size, $sum, &$id )
{
	connectdb();
	$req = "INSERT INTO files (filename,user_id,insert_date,update_date,".
		"category,subcategory,license_id,description,size,hash) ";
	$req .= "VALUES ('".mysql_real_escape_string( $filename )."','".
		mysql_real_escape_string( $uid )."',".
		"(SELECT NOW() ),".
		"(SELECT NOW() ),'".
		mysql_real_escape_string( $catid )."','".
		mysql_real_escape_string($subcatid)."',".
		mysql_real_escape_string($licenseid).",'".
		mysql_real_escape_string(htmlspecialchars($description))."',".
		mysql_real_escape_string( $size ).",'".
		"$sum')";
	$ret = mysql_query( $req );
	$id = mysql_insert_id();
	return( $ret );
}




function update_file($fid, $catid, $subcatid, $licenseid, $description )
{

	connectdb ();
	if( get_user_id( $_SESSION["remote_user"] ) != get_object_by_id( "files", $fid, "user_id" ) )
	{
		return;
	}

	$req = "UPDATE files SET `category`='".mysql_real_escape_string($catid)."',`subcategory`='".mysql_real_escape_string($subcatid)."',`license_id`='".mysql_real_escape_string($licenseid)."',`description`='".mysql_real_escape_string(htmlspecialchars($description))."',`update_date`=(SELECT NOW()) ";
	$req .= sprintf( "WHERE `id`='%s'", mysql_real_escape_string( $fid ) );
	return mysql_query( $req );
}

function increment_file_downloads ($fid)
{
	connectdb ();
	$req = sprintf( "UPDATE files SET downloads=downloads+1 WHERE `id`='%s'",
						mysql_real_escape_string( $fid ));
	mysql_query( $req );

	//$req = sprintf( "SELECT UNCOMPRESS(data) AS data FROM files WHERE `id`='%s'", mysql_real_escape_string($fid));
	//$result = mysql_query ($req);
	//$object = mysql_fetch_object ($result);
	//mysql_free_result ($result);

	return "";
}



function delete_file( $fid )
{
	connectdb();
	$fid = mysql_real_escape_string( $fid );
	if( mysql_query( sprintf( "DELETE FROM files WHERE `id`='%s'", $fid ) ) )
	{
		mysql_query( sprintf( "DELETE FROM comments WHERE `file_id`='%s'", $fid ) );
		mysql_query( sprintf( "DELETE FROM ratings WHERE `file_id`='%s'", $fid ) );
		return( TRUE );
	}
	return( FALSE );
}



function add_visitor_comment( $file, $comment, $user)
{
	$uid = get_user_id( $user );
	$comment = htmlspecialchars($comment, ENT_COMPAT, 'UTF-8' );

	if( $uid >= 0 )
	{
	 	connectdb();
	 	$req = sprintf( "INSERT INTO comments (user_id,file_id,text) VALUES('%s', '%s', '%s')",
					mysql_real_escape_string( $uid ),
					mysql_real_escape_string( $file ),
					mysql_real_escape_string( $comment ) );
	 	mysql_query( $req );
	}
}

/********************************************************************
                        OLD FUNCTIONS
				THESE WILL EVENTUALLY BE REMOVED
********************************************************************/
 

function connectdb() 
{
	global $DB_HOST, $DB_USER, $DB_PASS, $DB_DATABASE;
		// FIXME: TODO:  Change to use mysqli instead, these are deprecated
		@mysql_connect( $DB_HOST, $DB_USER, $DB_PASS );
		mysql_select_db( $DB_DATABASE );
}

function get_object_by_id_old( $table, $id, $field, $id_field = "id" )
{
	connectdb();
	
	$q = sprintf( "SELECT %s AS obj FROM `%s` WHERE `%s`='%s'",
				/*mysql_real_escape_string(*/ $field/* )*/,
				mysql_real_escape_string( $table ),
				mysql_real_escape_string( $id_field ),
				mysql_real_escape_string( $id ) );
	$result = mysql_query( $q );
	if( mysql_num_rows( $result ) > 0 )
	{
		$object = mysql_fetch_object( $result );
		mysql_free_result ($result);
		return $object->obj;
	}
	return( FALSE );
}


function get_id_by_object_old( $table, $field, $obj )
{
	connectdb();
	$q = sprintf( "SELECT id FROM `%s` WHERE `%s` LIKE '%s'",
				mysql_real_escape_string( $table ),
				mysql_real_escape_string( $field ),
				mysql_real_escape_string( $obj ) );
	$result = mysql_query( $q );
	if( mysql_num_rows( $result ) > 0 )
	{
		$object = mysql_fetch_object( $result );
		mysql_free_result ($result);
		return $object->id;
	}
	return( -1 );
}


 

function get_latest_old() {
	global $PAGE_SIZE;
 	connectdb();
	$req = "SELECT files.id, licenses.name AS license,size,realname,filename,users.login,".
		"categories.name AS category,subcategories.name AS subcategory,".
		"insert_date,update_date,description,files.downloads AS downloads FROM files ".
		"INNER JOIN categories ON categories.id=files.category ".
		"INNER JOIN subcategories ON subcategories.id=files.subcategory ".
		"INNER JOIN users ON users.id=files.user_id ".
		"INNER JOIN licenses ON licenses.id=files.license_id ".
	 	"ORDER BY files.update_date DESC LIMIT ". $PAGE_SIZE;
 	$result = mysql_query ($req);

 	echo "<h3>Latest Uploads</h3>".mysql_error()."\n";
	echo '<div class="lsp-table"><table class="table table-striped">';
	while ($object = mysql_fetch_object ($result)) {
		show_basic_file_info_old( $object, TRUE );
	}
	echo'</table></div>';
	mysql_free_result ($result);
}

function password_match_old ($pass,$user) {
 	connectdb ();
	$q = sprintf( "SELECT login FROM users WHERE password LIKE SHA1('%s') AND login LIKE '%s' AND loginFailureCount<6",
				mysql_real_escape_string( $pass ),
				mysql_real_escape_string( $user ) );
	$result = mysql_query( $q );
 	$object = mysql_fetch_object ($result);
 	mysql_free_result ($result);
 	if($object->login)
	{
		$q = sprintf( "UPDATE users SET loginFailureCount=0 WHERE login LIKE '%s'",
				mysql_real_escape_string( $user ) );
		$result = mysql_query( $q );
		return true;
	}
	else
	{
		$q = sprintf( "UPDATE users SET loginFailureCount=loginFailureCount+1 WHERE login LIKE '%s'",
				mysql_real_escape_string( $user ) );
		$result = mysql_query( $q );
	}
	return false;
 }



function myadd_user_old($login, $realname, $pass, $is_admin) {
 	connectdb ();
	$q = sprintf( "INSERT INTO users(login,realname,password,is_admin) VALUES ('%s','%s',SHA1('%s'),'%s')",
				mysql_real_escape_string( $login ),
				mysql_real_escape_string( $realname ),
				mysql_real_escape_string( $pass ),
				mysql_real_escape_string( $is_admin ) );
 	mysql_query( $q );
 	
}

function mychange_user_old($login,$realname,$pass) {
 	connectdb ();
	if($pass!='') {
		$q = sprintf( "UPDATE users SET `realname`='%s', `password`=SHA1('%s') WHERE `login` LIKE '%s'",
					mysql_real_escape_string( $realname ),
					mysql_real_escape_string( $pass ),
					mysql_real_escape_string( $login ) );
	} else {
		$q = sprintf( "UPDATE users SET `realname`='%s' WHERE `login` LIKE '%s'",
					mysql_real_escape_string( $realname ),
					mysql_real_escape_string( $login ) );
	}
 	mysql_query( $q );
 }
 



/********************************************************************
                        UTILITY FUNCTIONS
		THESE SHOULD BE MOVED TO A SEPARATE SHARED FILE
********************************************************************/
 

/*
 * Creates a bread-crumb style title for the table content
 * i.e All Content > Projects > Tutorials
 */
function create_title($array) {
	global $LSP_URL;
	if (!is_array($array)) {
		$array = array($array);
	} else {
		$one_element = one_element($array);
		if ($one_element) {
			$array = array($one_element);
		}
	}
	
	$title = "<a href=\"$LSP_URL\">All Content</a>";
	foreach ($array as $element) {
		if (isset($element) && trim($element) != '') {
			$title .= '&nbsp;&nbsp;<span class="fa fa-caret-right lsp-caret-right"></span>&nbsp;&nbsp;';
			$title .= trim($element);
		}
	}
	echo '<h3 class="lsp-title">' . $title . '</h3>';
}

/*
 * Returns the single element of an array
 * where only one element is not empty (null, or trimmed to blank)
 * or false if this does not apply
 */
function one_element($array) {
	if (is_array($array)) {
		$count = 0;
		foreach ($array as $element) {
			if (isset($element) && trim($element) != '') {
				$count++;
			}
		}
		if ($count == 1) {
			foreach ($array as $element) {
				if (isset($element) && trim($element) != '') {
					return $element;
				}
			}
		}
	}
	return false;
}

?>
