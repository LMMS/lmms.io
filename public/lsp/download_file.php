<?php
require_once('../utils.php');
require_once('utils.php');
require_once('dbo.php');

/*
 * Provides the file as a downloadable file through the browser including the
 * necessary header content to ensure the browser doesn't accidentally display
 * the file as a new page
 */
function download_file(int $file_id, string $file_name, string $type) {
	global $DATA_DIR;
	$file_path = $DATA_DIR . $file_id;
	if (file_exists($file_path)) {
		increment_file_downloads($file_id);
		$content_type = get_content_type($file_name);
		header("Content-type: $content_type");
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (!is_image($file_name)) {
			if (is_integer(strpos($user_agent,"msie")) && is_integer(strpos($user_agent, "win"))) {
				header("Content-Disposition:filename=\"$file_name\"");
			} else {
				header("Content-Disposition: attachment; filename=\"$file_name\"");
			}
			header("Content-Description: Download"); 	
		}
		ob_clean();
		flush();
		readfile($file_path);
	} else {
		if ($type == 'pfp') {
			header("Content-type: image/png");
			readfile("$DATA_DIR/../public/img/default-user.png");
		} else {
			require_once('dbo.php');
			header("HTTP/1.0 404 Not Found");
			echo "<h1>HTTP/1.0 404 Not Found</h1>";
			$link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			echo "Sorry, file was not found.  Please notify <a href=\"mailto:webmaster@lmms.io" . 
				"?subject=LSP 404&body=FYI: 404 Not Found: $link\">webmaster@lmms.io</a> of this error.";
		}
	}
	exit;
}

if (!GET_EMPTY('file') && !GET_EMPTY('name') && is_numeric(GET('file'))) {
	download_file(GET('file'), html_entity_decode(GET('name')), GET('type'));
} else {
	require_once('dbo.php');
	header("HTTP/1.0 400 Bad Request");
	echo "<h1>HTTP/1.0 400 Bad Request</h1>";
}
?>
