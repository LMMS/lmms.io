<?php
require_once('utils.php');

/*
 * Default database values.  Override with LSP_SECRETS below
 */
 $DB_TYPE = 'mysql';
 $DB_HOST = "localhost";
 $DB_USER = "someuser";
 $DB_PASS = "P@SSW0RD";
 $DB_DATABASE = "somedatabase";
 $PAGE_SIZE = 10;
 $TMP_DIR = '../../../';	// "tmp" is added automatically
 $DATA_DIR = '../../../';
 $LSP_URL = 'http://lmms.io/lsp/index.php';
/*
 * Query preferences
 * Note:  MySQL defaults to utf8mb4 charset since 5.5
 */
$DB_CHARSET = 'utf8mb4';
$PAGE_SIZE = 25;
$MAX_LOGIN_ATTEMPTS = 6;

/*
 * Global paths
 */
$TMP_DIR = $_SERVER['DOCUMENT_ROOT'] . '/../tmp/';
$DATA_DIR = $_SERVER['DOCUMENT_ROOT'] . '/../tmp/';
$LSP_URL = '/lsp/';

/*
 * By default, the LSP will use the default database values defined above
 * however, for production environments, the defaults must be overridden.  This
 * is done in a separate config file defined as $LSP_CONFIG which should be out
 * of the document root and inaccessible from a webpage.
 */
$LSP_SECRET = '/home/deploy/secrets/LSP_SECRETS';
if (file_exists($LSP_SECRET)) { include($LSP_SECRET); }

/*
 * Override constants with those from $LSP_SECRET, if available
 */
$DB_TYPE = defined('DB_TYPE') ? DB_TYPE : $DB_TYPE;
$DB_HOST = defined('DB_HOST') ? DB_HOST : $DB_HOST;
$DB_USER = defined('DB_USER') ? DB_USER : $DB_USER;
$DB_PASS = defined('DB_PASS') ? DB_PASS : $DB_PASS;
$DB_DATABASE = defined('DB_DATABASE') ? DB_DATABASE : $DB_DATABASE;
$TMP_DIR = defined('TMP_DIR') ? TMP_DIR : $TMP_DIR;
$DATA_DIR = defined('DB_PASS') ? DATA_DIR : $DATA_DIR;
$LSP_URL = defined('LSP_URL') ? LSP_URL : $LSP_URL;

/*
 * DANGER! When set to true will attempt to echo database statements and values to screen
 */
define('DBO_DEBUG', false);

/*
 * Tables allowed to perform dynamic SQL queries/updates against
 */ 
define('DBO_TABLES', 'categories,comments,files,filetypes,licenses,ratings,subcategories,users');

/*
 * Valid root functions to be looped over and processed by index.php. This order is important
 * as a user could potentially key in many functions, but we only want to process one.
 */
define('POST_FUNCS', 'rate,comment,content,action,search,q,account');

/*
 * MySQL functions allowed to be called around non-specific columns
 * All function names should be lower-case
 */
define('DBO_FUNCS', 'count,avg');

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
function debug_out($object) {
	if (DBO_DEBUG) {
		error_log($object, 0);
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
 * Abstract object id to object value wrapper.  Has sanitization checks to protect
 * against misuse.
 */
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
		debug_out("SELECT $field FROM $table WHERE $id_field='$id'");
		$stmt->bindParam(':id', $id);
		$object = null;
		if ($stmt->execute()) {
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$object = $object[$field];
				debug_out($object);
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
function get_id_by_object($table, $field, $value) {
	$id = -1;
	// Sanitize column and table values
	$table = sanitize($table);
	$field = sanitize($field);
	
	// Validate the table name from a white-list
	if (is_valid_table($table))  {
		$dbh = &get_db();
		$stmt = $dbh->prepare("SELECT id FROM $table WHERE LOWER($field) = LOWER(:value)");
		debug_out("SELECT id FROM $table WHERE LOWER($field) = LOWER('$value')");
		$stmt->bindParam(':value', $value);
		if ($stmt->execute()) {
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$id = $object['id'];
				break;
			}
		}
		$stmt = null;
		$dbh = null;
		debug_out("> id=\"$id\"");
	}
	
	return $id;
}

/*
 * Gets the latest uploads and displays them in a table.
 * This is the default contents of the index.php page.
 */
function get_latest() {
	global $PAGE_SIZE;
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT files.id, licenses.name AS license,size,realname,filename,users.login,
		categories.name AS category,subcategories.name AS subcategory,
		insert_date,update_date,description,files.downloads AS downloads,
		(SELECT COUNT(file_id) FROM comments WHERE file_id=files.id) AS comments,
		(SELECT COALESCE(AVG(stars), 0) FROM ratings WHERE file_id=files.id) AS rating,
		(SELECT COUNT(id) FROM ratings WHERE file_id=files.id) AS rating_count
		FROM files
		INNER JOIN categories ON categories.id=files.category 
		INNER JOIN subcategories ON subcategories.id=files.subcategory 
		INNER JOIN users ON users.id=files.user_id 
		INNER JOIN licenses ON licenses.id=files.license_id 
		ORDER BY files.insert_date DESC
	 	LIMIT ' . sanitize($PAGE_SIZE));
		$ret = array();
		if ($stmt->execute()) {
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$ret[] = $object;
			}
		}
		$stmt = null;
		$dbh = null;
		return $ret;
}

/*
 * Attempts to match a password against a given account
 * so as long as the login attempts are less than $MAX_LOGIN_ATTEMPTS
 */
function password_match($pass, $user) {
	global $MAX_LOGIN_ATTEMPTS;
	$dbh = &get_db();
	$stmt = $dbh->prepare('SELECT login FROM users WHERE LOWER(password) = LOWER(SHA1(:pass)) AND LOWER(login) = LOWER(:user) AND loginFailureCount < :max_login_attempts');
	debug_out("SELECT login FROM users WHERE LOWER(password) = LOWER(SHA1($pass)) AND LOWER(login) = LOWER($user) AND loginFailureCount < $MAX_LOGIN_ATTEMPTS");
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
	return $return_val;
}

/*
 * Increments or resets the login-failure count for the specified user
 */
function set_failure_count($user, $incriment=false) {
	$dbh = &get_db();
	if ($incriment) {
		$stmt = $dbh->prepare('UPDATE users SET loginFailureCount = loginFailureCount + 1 WHERE LOWER(login) = LOWER(:user)');
		debug_out("UPDATE users SET loginFailureCount = loginFailureCount + 1 WHERE LOWER(login) = LOWER($user)");
	} else {
		$stmt = $dbh->prepare('UPDATE users SET loginFailureCount=0 WHERE LOWER(login) = LOWER(:user)');
		debug_out("UPDATE users SET loginFailureCount=0 WHERE login = $user");
	}
	
	$stmt->bindParam(':user', $user);
	$stmt->execute();
	$stmt = null;
	$dbh = null;
}

/*
 * Returns whether or not the specified user-id is an administrator
 */
function is_admin($uid) {
	return get_object_by_id("users", $uid, "is_admin");
}

/*
 * Adds a user to the database with LSP add/edit access identified by 
 * $login, $realname, $password, $is_admin
 */
function add_user($login, $realname, $pass, $is_admin = false) {
	$dbh = &get_db();
	$admin = $is_admin ? 1 : 0;
	$stmt = $dbh->prepare('INSERT INTO users(login, realname, password, is_admin, loginFailureCount) VALUES(:login, :realname, SHA1(:password), :is_admin, 0)');
	debug_out("INSERT INTO users(login, realname, password, is_admin, loginFailureCount) VALUES($login, $realname, SHA1($pass), $is_admin, 0)");
	$stmt->bindParam(':login', $login);
	$stmt->bindParam(':realname', $realname);
	$stmt->bindParam(':password', $pass);
	$stmt->bindParam(':is_admin', $admin);
	$success = $stmt->execute();
	$stmt = null;
	$dbh = null;
	return $success;
}

 /*
  * Update the realname and/or password of the specified user
  */
 function change_user($login, $realname, $password) {
	$dbh = &get_db();
	if ($password != '') {
		$stmt = $dbh->prepare('UPDATE users SET realname=:realname, password=SHA1(:password) WHERE LOWER(login)=LOWER(:login)');
		debug_out("UPDATE users SET realname=$realname, password=SHA1($password) WHERE LOWER(login)=LOWER($login)");
		$stmt->bindParam(':realname', $realname);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':login', $login);
	} else {
		$stmt = $dbh->prepare('UPDATE users SET realname=:realname WHERE LOWER(login)=LOWER(:login)');
		debug_out("UPDATE users SET realname=$realname WHERE LOWER(login)=LOWER($login)");
		$stmt->bindParam(':realname', $realname);
		$stmt->bindParam(':login', $login);
	}
	$stmt->execute();
	$stmt = null;
	$dbh = null;
 }

/*
 * Get list of top level categories
 */
function get_categories() {
	$dbh = &get_db();
	$ret = array();
	
	$stmt = $dbh->prepare(
		'SELECT categories.name AS name, COUNT(files.id) AS file_count, categories.id AS id ' .
		'FROM categories LEFT JOIN files ON files.category = categories.id ' .
		'GROUP BY categories.name, categories.id ' .
		'ORDER BY categories.id '
	);
	
	$sort = GET('sort', 'date');
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$name = $object['name'] . ' (' . $object['file_count'] . ')';
			$url = htmlentities("/lsp/?action=browse&category=" . rawurlencode($object['name']) . "&sort=$sort" );
			if (!GET_EMPTY('category') && GET('category') == $object['name']) {
				$subcategory = get_subcategories($object['name'], $object['id']);
				$ret[] = array($name, $url, $subcategory);
				continue;
			}
			$ret[] = array($name, $url);
		}
	}
	$stmt = null;
	$dbh = null;
	return $ret;
}

/*
 * Get list of subcategories matching provided category ID
 */
function get_subcategories($category, $id) {
	global $LSP_URL;
	$dbh = &get_db();
	$ret = array();
	
	$stmt = $dbh->prepare(
		'SELECT subcategories.name AS name, COUNT(files.id) AS file_count ' .
		'FROM subcategories ' .
		'LEFT JOIN files ON files.subcategory = subcategories.id AND files.category=:id ' .
		'WHERE subcategories.category=:id GROUP BY subcategories.name ORDER BY subcategories.name'
	);
	$stmt->bindParam(':id', $id);
	
	$sort = rawurlencode(GET('sort', 'date'));
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$name = $object['name'] . ' (' . $object['file_count'] . ')';
			$ret[$name] = 
				htmlentities("/lsp/?action=browse&category=" . $category . 
				"&subcategory=" . rawurlencode($object['name']) . "&sort=$sort" );
		}
	}
	$stmt = null;
	$dbh = null;
	return $ret;
}

/*
 * Returns <option> tags for each category-subcategory pair found in the database
 * containing references to the supplied file extension.
 * Usage:
 * 		echo get_categories_for_ext('.mmpz', 'Projects-Ambient');
 *		// or
 *		echo get_categories_for_ext('.mmpz');
 *
 */
function get_categories_for_ext($extension, $default = '') {
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT CONCAT(categories.name, \' - \', subcategories.name) AS fullname FROM filetypes ' .
		'INNER JOIN categories ON categories.id=filetypes.category ' .
		'INNER JOIN subcategories ON subcategories.category=categories.id ' . 
		'WHERE LOWER(extension) = LOWER(:extension) ' .
		'ORDER BY categories.name, subcategories.name'
	);
	
	$stmt->bindParam(':extension', $extension);
	$html = '';
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$fullname = htmlspecialchars($object['fullname']);
			$selected = strtolower($fullname) == strtolower($default) ? ' selected' : '';
			$html .= "<option$selected>$fullname</option>";
		}
	}

	$stmt = null;
	$dbh = null;
	return $html;
}

/*
 * Returns <li> tags for each extension type found in the database
 * Usage:
 * 		echo get_extensions();
 */
function get_extensions() {
	$dbh = &get_db();
	$stmt = $dbh->prepare('SELECT distinct extension from filetypes');
	$html = '';
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$name = $object['extension'];
			$html .= "<li>$name</li>";
		}
	}
	$stmt = null;
	$dbh = null;
	return $html;
}

/*
 * Returns <option> tags for each license found in the database
 * Usage:
 * 		echo get_licenses('BSD');
 *		// or
 *		echo get_licenses();
 */
function get_licenses($default = 'Creative Commons (by)') {
	$dbh = &get_db();
	$stmt = $dbh->prepare('SELECT name FROM licenses');
	$html = '';
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$name = $object['name'];
			$selected = strtolower($name) == strtolower($default) ? 'selected' : '';
			$html .= "<option $selected>$name</option>";
		}
	}
	$stmt = null;
	$dbh = null;
	return $html;
}

/*
 * Gets a CSV list of file_ids matching with comments matching the search string
 */
function search_comments($search) {
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT DISTINCT(file_id) FROM comments ' . 
		'WHERE text LIKE :search'
	);
	$search = "%{$search}%";
	$stmt->bindParam(':search', $search);
	$file_array = array();
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			array_push($file_array, $object['file_id']);
		}
	}
	return implode(',', $file_array);
}

/*
 * Returns a list of <blockquote> items containing all comments for a particular file
 * with all comments made by the owner of said file in an alternate style
 */
function get_comments($file_id) {
	global $LSP_URL;
	$dbh = &get_db();
	$ret = array();
	$stmt = $dbh->prepare(
		'SELECT users.realname, users.login, comments.user_id as commentuser, ' . 
		'files.user_id as fileuser, date,text FROM comments ' . 
		'INNER JOIN users ON users.id=comments.user_id ' . 
		'INNER JOIN files ON files.id=comments.file_id ' . 
		'WHERE file_id=:file_id ORDER BY date'
	);
	$stmt->bindParam(':file_id', $file_id);
	$html = '';
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$comment = $object['text'];
			// Bold comments made by the original author
			$ret[] = $object;
		}
	}
		
	$stmt = null;
	$dbh = null;
	return $ret;
}

/*
 * Returns the category name (i.e. "Presets") for the given file id
 */
function get_file_category($file_id) {
	global $LSP_URL;
	$dbh = &get_db();
	$stmt = $dbh->prepare('SELECT categories.name FROM files INNER JOIN categories ON categories.id=files.category WHERE files.id=:file_id');
	$stmt->bindParam(':file_id', $file_id);
	$return_val = null;
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$return_val = $object['name'];
			break;
		}
	}
	return $return_val;
}

/*
 * Returns the subcategory name (i.e. "Basses") for the given file id
 */
function get_file_subcategory($file_id) {
	global $LSP_URL;
	$dbh = &get_db();
	$stmt = $dbh->prepare('SELECT subcategories.name FROM files INNER JOIN subcategories ON subcategories.id=files.subcategory WHERE files.id=:file_id');
	$stmt->bindParam(':file_id', $file_id);
	$return_val = null;
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$return_val = $object['name'];
			break;
		}
	}
	return $return_val;
}

/*
 * Forecasts the total size of the database result set returned
 * by get_results for proper pagination
 */
function get_results_count($category, $subcategory = '', $search = '', $user_id = '', $additional_items = '') {
	$dbh = &get_db();
	$return_val = 0;
	$stmt = $dbh->prepare(
		'SELECT COUNT(files.id) as file_count FROM files ' .
		'INNER JOIN categories ON categories.id=files.category ' .
		'INNER JOIN subcategories ON subcategories.id=files.subcategory ' .
		'INNER JOIN users ON users.id=files.user_id WHERE ' .
		(strlen($user_id) ? 'files.user_id=:user_id' : 'true') . ' AND ' .
		(strlen($category) ? 'categories.name=:category' : 'true') . ' AND ' .
		(strlen($subcategory) ? 'subcategories.name=:subcategory' : 'true') . ' AND ' .
		(strlen($search) ? " (files.filename LIKE :search OR users.login LIKE :search OR users.realname LIKE :search $additional_items)" : 'true')
	);
	
	if (strlen($user_id)) { $stmt->bindParam(':user_id', $user_id); }
	if (strlen($category)) { $stmt->bindParam(':category', $category); }
	if (strlen($subcategory)) {
		$subcategory = htmlspecialchars_decode($subcategory);
		$stmt->bindParam(':subcategory', $subcategory);
	}
	if (strlen($search)) { $search = "%{$search}%"; $stmt->bindParam(':search',$search); }
		
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$return_val = $object['file_count'];
			break;
		}
	}
	
	$stmt = null;
	$dbh = null;
	return $return_val;
}

/*
 * Displays a table of search results, usually based on category, subcategory or search
 * filters
 */
function get_results($category, $subcategory, $sort = '', $search = '', $user_name = '', $order = 'DESC', $comment_search = false) {
	global $PAGE_SIZE;
	global $LSP_URL;
	$user_id = '';
	$order = in_array(trim(strtoupper($order)), array('DESC', 'ASC')) ? trim(strtoupper($order)) : 'DESC';
	if (strlen($user_name)) { $user_id = get_user_id($user_name);} 

	$additional_items = '';
	// Get an additional CSV list of files with comments match
	if (strlen($search) && $comment_search) {
		$additional_items = search_comments($search);
		$additional_items = strlen($additional_items) ? "OR files.id IN ($additional_items)" : '';
	}
	
	$count = get_results_count($category, $subcategory, $search, $user_id, $additional_items);
	$user_id = $user_id == -1 ? '' : $user_id;
		
	$order_by = 'files.insert_date';
	switch ($sort) {
		case 'downloads' : $order_by = 'downloads_per_day'; break;
		case 'rating' : $order_by = "rating $order, rating_count"; break;
		case 'comments' : break; //FIXME: TODO: Add support for sorting by comments
	}
	
	$start = intval(GET('page', 0) * $PAGE_SIZE);
	
	$dbh = &get_db();
	$ret = array();
	$stmt = $dbh->prepare(
		'SELECT files.id, licenses.name AS license, size,realname, filename,
			users.login, categories.name AS category, subcategories.name AS subcategory,
			files.downloads*files.downloads/(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(files.insert_date)) AS downloads_per_day,
			files.downloads AS downloads, insert_date, update_date, description,
			(SELECT COUNT(file_id) FROM comments WHERE file_id=files.id) AS comments,
			(SELECT COUNT(id) FROM ratings WHERE file_id=files.id) AS rating_count,
			(SELECT COALESCE(AVG(stars), 0) FROM ratings WHERE file_id=files.id) AS rating
			FROM files
		INNER JOIN categories ON categories.id=files.category
		INNER JOIN subcategories ON subcategories.id=files.subcategory
		INNER JOIN users ON users.id=files.user_id
		INNER JOIN licenses ON licenses.id=files.license_id
		WHERE ' .
		(strlen($user_id) ? 'files.user_id=:user_id' : 'true') . ' AND ' .
		(strlen($category) ? 'files.category=(SELECT id FROM categories WHERE NAME=:category)' : 'true') . ' AND ' .
		(strlen($subcategory) ? 'files.subcategory IN (SELECT id FROM subcategories WHERE NAME=:subcategory)' : 'true') . ' AND ' .
		(strlen($search) ? "(files.filename LIKE :search OR users.login LIKE :search OR users.realname LIKE :search $additional_items)" : 'true') . ' ' .
		'ORDER BY ' . $order_by . " $order " .
		"LIMIT $start, $PAGE_SIZE"
	);
	
	if (strlen($user_name)) { $stmt->bindParam(':user_id', $user_id); }
	if (strlen($category)) { $stmt->bindParam(':category', $category); }
	if (strlen($subcategory)) {
		$subcategory = htmlspecialchars_decode($subcategory);
		$stmt->bindParam(':subcategory', $subcategory);
	}
	if (strlen($search)) { $search = "%{$search}%"; $stmt->bindParam(':search', $search); }
	
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$ret[] = $object;
		}
	}
	debug_out(var_export($ret, true));
	$stmt = null;
	$dbh = null;
	return array($count, $ret);
}

/*
 * Returns the index of the filetype specified by 
 * extension and category
 * i.e. get_file_type('.mmpz', 'Projects');
 */
function get_filetype_id($extension, $category) {
	$filetype_id = -1;
	$category_id = get_category_id($category);
	
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT id FROM filetypes ' .
		'WHERE category = :category_id and LOWER(extension) = LOWER(:extension)'
	);
	$fixed_extension = fix_extension($extension);
	$stmt->bindParam(':category_id', $category_id);
	$stmt->bindParam(':extension', $fixed_extension);
	
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$filetype_id = $object['id'];
			break;
		}
	}
	$stmt = null;
	$dbh = null;
	return $filetype_id;
}

/*
 * Make sure the supplied string starts with a '.'
 */
function fix_extension($extension) {
	return strpos($extension, '.') === 0 ? $extension : ".$extension";
}

/*
 * Inserts the supplied extension and category pair into the
 * filteypes table.  Creates the appropriate entry in the category
 * table if it doesn't already exist.
 */
function insert_filetype($extension, $category) {
	$category_id = insert_category($category);
	$filetype_id = get_filetype_id($extension, $category);
	// Extension/category pair doesn't exist yet, insert our row
	if ($filetype_id < 0) {
		$dbh = &get_db();
		$stmt = $dbh->prepare(
			'INSERT INTO filetypes (extension, category) VALUES(LOWER(:extension), :category_id)'
		);

		$fixed_extension = fix_extension($extension);
		$stmt->bindParam(':extension', $fixed_extension);
		$stmt->bindParam(':category_id', $category_id);
		
		// Inserted successfully, find our filetype id
		if ($stmt->execute()) {
			$filetype_id = get_filetype_id($fixed_extension, $category);
		}
		$stmt = null;
		$dbh = null;
	}
	
	return $filetype_id;
}

/*
 * Inserts a category with the given name and returns the index
 * Note:  Duplicate category names are not allowed.  Silently returns
 * existing index if it already exists.
 */
function insert_category($category) {
	$category_id = get_id_by_object('categories', 'name', $category);
	if ($category_id < 0) {
		$dbh = &get_db();
		$stmt = $dbh->prepare('INSERT INTO categories (name) VALUES(:category)');
		$stmt->bindParam(':category', $category);
		if ($stmt->execute()) {
			$stmt = null;
			$category_id = get_id_by_object('categories', 'name', $category);
		}
		$dbh = null;
	}
	return $category_id;
}

/*
 * The page which displays the file details, i.e. ?action=show&file=1234
 * This page must include a download button, links to edit, comment, delete, rate
 * as well as all information that's already displayed in the original search results.
 */
function show_file($file_id, $user, $success = null): array {
	global $LSP_URL, $DATA_DIR;
	$ret = array();
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT licenses.name AS license, size, realname, filename, users.login, 
		categories.name AS category, subcategories.name AS subcategory, 
		insert_date, update_date, description, downloads, files.id, filename, 
		(SELECT COUNT(file_id) FROM comments WHERE file_id=files.id) AS comments,
		(SELECT COALESCE(AVG(stars), 0) FROM ratings WHERE file_id=files.id) AS rating,
		(SELECT COUNT(id) FROM ratings WHERE file_id=files.id) AS rating_count
		FROM files
		INNER JOIN categories ON categories.id=files.category 
		INNER JOIN subcategories ON subcategories.id=files.subcategory 
		INNER JOIN users ON users.id=files.user_id 
		INNER JOIN licenses ON licenses.id=files.license_id 
		WHERE files.id=:file_id'
	);
	$stmt->bindParam(':file_id', $file_id);
	
	$found = false;
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$object['url'] = get_file_url($file_id);
			$url = 'download_file.php?file=' . $object['id'] . '&name=' . urlencode($object['filename']);
			$object['download'] = $url;
			$object['comment_section'] = get_comments($file_id);
			$object['description'] = ($object['description'] != '') ? $object['description'] : null;
			if (($project_data = read_project($object['id'])) != null) {
				$object['lmms_version'] = $project_data->attributes()['creatorversion'];
			}
			$object['session_rating'] = get_user_rating($file_id, $user);
			if (is_image($url)) {
				$object['thumb'] = scale_image($DATA_DIR . $file_id, 300, parse_extension($url));
			}
			$ret[] = $object;
			$title = array($object['category'], $object['subcategory'], get_file_url($file_id));
			if ($success == null) {
				// Do nothing
			} else if ($success === true) {
				display_success("Updated successfully", $title);
			} else if ($success === false) {
				display_error("Update failed.", $title);
			} else {
				display_success("$success", $title);
			}
			
			$found = true;
			break;
		}
	}

	$stmt = null;
	$dbh = null;
	return $ret;
}

/*
 * Used when updating a rating for a particular file
 * (or displaying the logged-in user's rating for a particular file)
 */
function get_user_rating($file_id, $user) {
	$return_val = 0;
	$user_id = get_user_id($user);
	if ($user_id >= 0) {
		$dbh = &get_db();
		$stmt = $dbh->prepare('SELECT COUNT(stars) AS stars_count FROM ratings WHERE file_id=:file_id AND user_id=:user_id');
		$stmt->bindParam(':file_id', $file_id);
		$stmt->bindParam(':user_id', $user_id);
		if ($stmt->execute()) {
			while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if ($object['stars_count'] >= 1) {
					$stmt = $dbh->prepare('SELECT stars FROM ratings WHERE file_id=:file_id AND user_id=:user_id');
					$stmt->bindParam(':file_id', $file_id);
					$stmt->bindParam(':user_id', $user_id);
					if ($stmt->execute()) {
						while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$return_val = $object['stars'];
							break;
						}
					}
				}
			}
		}
	}
	return $return_val;
}

/*
 * Updates the $star rating value in the databsae for the specified $file_id 
 * and the specified $user ($user should always be currently logged-in user)
 * and returns the new rating value which has been applied to the database.
 */
function update_rating($file_id, $stars, $user) {
	// Incorrect $stars value supplied
	if (!isset($stars) || trim($stars) == '' || $stars < 1 || $stars > 5) {
		echo '<h3 class="text-danger">Invalid rating: ' . 
			(isset($stars) ? sanitize($stars) : '(empty)') . '</h3>';
			return;
	}
	
	// Incorrect $file_id supplied
	if (!is_numeric($file_id) || !isset($file_id) || $file_id < 0) {
		echo '<h3 class="text-danger">Invalid file_id: ' . 
			(is_int($file_id) ? '"' . intval($file_id) . '"' : 
			(isset($file_id) ?  '"' . sanitize($file_id) . '"' : '(empty)'))  . '</h3>';
			return;
	}
	
	// Incorrect user name supplied
	$user_id = get_user_id($user);
	if ($user_id < 0) {
		echo '<h3 class="text-danger">Invalid user: ' . 
			(isset($user) ? '"' . sanitize($user) . '"' : '(empty)')  . '</h3>';
			return;
	}
	
	$dbh = &get_db();
	if ($user_id >= 0) {
		// Makes sure that the user who is rating is not the owner of the file
		if (get_file_owner($file_id) == get_user_id(SESSION())) {
			echo '<h3 class="text-danger">You cannot rate your own file.<h3>';
			return;
		}
		$stmt = null;
		if (get_user_rating($file_id, $user) > 0) {
			$stmt = $dbh->prepare('UPDATE ratings SET stars=:stars WHERE file_id=:file_id AND user_id=:user_id');
		} else {
			$stmt = $dbh->prepare('INSERT INTO ratings(file_id, user_id, stars) VALUES(:file_id, :user_id, :stars)');
		}
		$stmt->bindParam(':file_id', $file_id);
		$stmt->bindParam(':stars', $stars);
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();
		$stmt = null;
	}
	$dbh = null;
	return get_user_rating($file_id, $user);
}

/*
 * Inserts an row into the files table
 */
function insert_file($filename, $user_id, $category_id, $subcategory_id, $license_id, $description, $size, $hash) {
	if (DBO_DEBUG) {
		debug_out("<code>insert_file($filename, $user_id, $category_id, $subcategory_id, $license_id, $description, $size, $hash)</code>");
	}
	$dbh = &get_db();
	$return_val = -1;
	$stmt = $dbh->prepare(
		'INSERT INTO files (' . 
			'filename, user_id, insert_date, update_date, category, ' . 
			'subcategory, license_id, description, size, hash' . 
		') VALUES (' .
			':filename, :user_id, NOW(), NOW(), :category_id, ' .
			':subcategory_id, :license_id, :description, :size, :hash' .
		')'
	);
	$stmt->bindParam(':filename', $filename);
	$stmt->bindParam(':user_id', $user_id);
	$stmt->bindParam(':category_id', $category_id);
	$stmt->bindParam(':subcategory_id', $subcategory_id);
	$stmt->bindParam(':license_id', $license_id);
	$stmt->bindParam(':description', $description);
	$stmt->bindParam(':size', $size);
	$stmt->bindParam(':hash', $hash);
	if ($stmt->execute()) {
		$return_val = $dbh->lastInsertId('id');
	}
	$stmt = null;
	$dbh = null;
	return $return_val;
}

/*
 * Updates a row into the files table
 */
function update_file($file_id, $category_id, $subcategory_id, $license_id, $description) {
	$dbh = &get_db();
	$return_val = false;
	$stmt = $dbh->prepare(
		'UPDATE files SET ' .
			'update_date=NOW(), category=:category_id, ' . 
			'subcategory=:subcategory_id, license_id=:license_id, ' . 
			'description=:description ' . 
		'WHERE id=:file_id'
	);
	$stmt->bindParam(':file_id', $file_id);
	$stmt->bindParam(':category_id', $category_id);
	$stmt->bindParam(':subcategory_id', $subcategory_id);
	$stmt->bindParam(':license_id', $license_id);
	$stmt->bindParam(':description', $description);
	if ($stmt->execute()) {
		$return_val = true;
	}
	$stmt = null;
	$dbh = null;
	return $return_val;
}

/*
 * Increments the file download count by +1 in the files table
 */
function increment_file_downloads($file_id) {
	$dbh = &get_db();
	$return_val = false;
	$stmt = $dbh->prepare('UPDATE files SET downloads=downloads+1 WHERE id=:file_id');
	$stmt->bindParam(':file_id', $file_id);
	if ($stmt->execute()) {
		$return_val = true;
	}
	$stmt = null;
	$dbh = null;
	return $return_val;
}

/*
 * Deletes a file by purging it from all relevant tables (files, comments, ratings)
 */
function delete_file($file_id) {
	$dbh = &get_db();
	$return_val = false;
	$stmt1 = $dbh->prepare('DELETE FROM files WHERE id=:file_id');
	$stmt2 = $dbh->prepare('DELETE FROM comments WHERE id=:file_id');
	$stmt3 = $dbh->prepare('DELETE FROM ratings WHERE id=:file_id');
	$stmt1->bindParam(':file_id', $file_id);
	$stmt2->bindParam(':file_id', $file_id);
	$stmt3->bindParam(':file_id', $file_id);
	if ($stmt1->execute()) {
		$stmt2->execute();
		$stmt3->execute();
		$return_val = true;
	}
	$stmt1 = null;
	$stmt2 = null;
	$stmt3 = null;
	$dbh = null;
	return $return_val;
}

/*
 * Adds a comment to the specified file
 */
function add_visitor_comment($file_id, $comment, $user) {
	$user_id = get_user_id($user);
	$return_val = false;
	
	if ($file_id >= 0 && $user_id >= 0) {
		$dbh = &get_db();
		$stmt = $dbh->prepare('INSERT INTO comments (user_id, file_id, text) VALUES(:user_id, :file_id, :text)');
		$stmt->bindParam(':user_id', $user_id);
		$stmt->bindParam(':file_id', $file_id);
		$stmt->bindParam(':text', $comment);
		if ($stmt->execute()) {
			$return_val = true;
		}
		$stmt = null;
		$dbh = null;
	}
	return $return_val;
}

/*
 * Build DOM content for optional XML API.  An application can call upon 
 * this using $LSP_URL/web_resources.php.  At the time of the LSP upgrade (2014) no 
 * applications are actively using this API.
 */
function get_web_resources() {
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT files.filename AS fname, files.hash AS hash, ' . 
		'categories.name AS catname, subcategories.name AS subcatname, ' . 
		'files.size AS size, files.update_date AS date, ' .
		'users.login AS author FROM files ' .
		'INNER JOIN categories ON categories.id=files.category ' .
		'INNER JOIN subcategories ON subcategories.id=files.subcategory ' .
		'INNER JOIN users ON users.id=files.user_id ' . 
		'ORDER BY files.id'
	);
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<file><name>" . htmlspecialchars($object['fname'], ENT_COMPAT, 'UTF-8') . "</name>" .
				"<hash>$object[hash]</hash><size>$object[size]</size><date>$object[date]</date>" .
				"<author>" . htmlspecialchars($object['author'], ENT_COMPAT, 'UTF-8') . "</author>" .
				"<dir>" . htmlspecialchars("$object[catname]/$object[subcatname]", ENT_COMPAT, 'UTF-8') . "</dir>" .
				"</file>";
		}
	}
	$stmt = null;
	$dbh = null;
}

/*
 * Retrieve the subcategory id from the database.
 * Since subcategories can have identical names, a parent category id
 * must also be provided
 */
function get_subcategory_id($category_id, $subcategory) {
	$subcategory_id = -1;
	if ($category_id < 0) {
		return $subcategory_id;
	}
	$dbh = &get_db();
	$stmt = $dbh->prepare(
		'SELECT id FROM subcategories ' .
		'WHERE category = :category_id and LOWER(name) = LOWER(:subcategory)'
	);
	$stmt->bindParam(':category_id', $category_id);
	$stmt->bindParam(':subcategory', $subcategory);
	
	if ($stmt->execute()) {
		while ($object = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$subcategory_id = $object['id'];
			break;
		}
	}
	$stmt = null;
	$dbh = null;
	return $subcategory_id;
}

/*
 * Convenience Functions
 */
function get_user_id($login) { return get_id_by_object('users', 'login', $login); }
function get_user_realname($login) { return get_object_by_id('users', get_user_id($login), 'realname'); }
function get_file_name($file_id){ return get_object_by_id('files', $file_id, 'filename'); }
function get_file_owner($file_id) {	return get_object_by_id('files', $file_id, 'user_id'); }
function get_file_description($file_id) { return get_object_by_id('files', $file_id, 'description'); }
function get_file_license($file_id) { return( get_object_by_id('files', $file_id, 'license_id')); }
function get_file_comment_count($file_id) { return get_object_by_id('comments', $file_id, '1', 'file_id', 'count'); }
function get_file_rating_count($file_id) { return get_object_by_id('ratings', $file_id, '1', 'file_id', 'count'); }
function get_file_rating($file_id) { return get_object_by_id('ratings', $file_id, 'stars', 'file_id', 'avg'); }
function get_file_downloads($file_id) { return( get_object_by_id('files', $file_id, 'downloads')); }
function get_category_id($category) { return get_id_by_object('categories', 'name', $category); }
function get_license_id($license) {	return  get_id_by_object('licenses', 'name', $license); }
function get_license_name($license_name) { return get_object_by_id('licenses', $license_name, 'name'); }

?>
