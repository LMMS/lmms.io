<?php

/*
 * Provides the file as a downloadable file through the browser
 * including the necessary header content to ensure the browser
 * doesn't accidentally display the file as a new page
 */
require_once('config.php');
require_once('dbo.php');

function download_file($fid, $filename) {
    global $DATA_DIR;
	$fn = $DATA_DIR . $fid;
	if (file_exists($fn)) {
		increment_file_downloads($fid);
		header("Content-type: application/force-download");
		header(((is_integer(strpos($user_agent,"msie")))&&(is_integer(strpos($user_agent, "win"))))?"Content-Disposition:filename=\"$filename\"":"Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Description: Download"); 	
		ob_clean();
		flush();
		readfile($fn);
	} else {
		require_once('dbo.php');
		header("HTTP/1.0 404 Not Found");
		echo "<h1>HTTP/1.0 404 Not Found</h1>";
		echo "Sorry, <code>$filename (file $fid)</code> was not found.  Please notify <a href=\"mailto:webmaster@lmms.io\">webmaster@lmms.io</a> of this error.";
	}
	exit;
}

if (!GET_EMPTY('file') && !GET_EMPTY('name')) {
	download_file(GET('file'), GET('name'));
}
?>
