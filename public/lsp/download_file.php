<?php

/*
 * Provides the file as a downloadable file through the browser including the
 * necessary header content to ensure the browser doesn't accidentally display
 * the file as a new page
 */
require_once('config.php');
require_once('dbo.php');

function download_file($file_id, $file_name) {
    global $DATA_DIR;
	$file_path = $DATA_DIR . $file_id;
	if (file_exists($file_path)) {
		increment_file_downloads($file_id);
		header("Content-type: application/force-download");
		header(((is_integer(strpos($user_agent,"msie")))&&(is_integer(strpos($user_agent, "win"))))?"Content-Disposition:filename=\"$file_name\"":"Content-Disposition: attachment; filename=\"$file_name\"");
		header("Content-Description: Download"); 	
		ob_clean();
		flush();
		readfile($file_path);
	} else {
		require_once('dbo.php');
		header("HTTP/1.0 404 Not Found");
		echo "<h1>HTTP/1.0 404 Not Found</h1>";
		$link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		echo "Sorry, file was not found.  Please notify <a href=\"mailto:webmaster@lmms.io" . 
			"?subject=LSP 404&body=FYI: 404 Not Found: $link\">webmaster@lmms.io</a> of this error.";
	}
	exit;
}

if (!GET_EMPTY('file') && !GET_EMPTY('name')) {
	download_file(GET('file'), GET('name'));
}
?>
