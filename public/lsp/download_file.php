<?php

require_once('config.php');
require_once('dbo.php');

function dl_file ($fid,$filename)
{
        global $DATA_DIR;
	$fn = $DATA_DIR.$fid;
	if (file_exists($fn)) {
		increment_file_downloads( $fid );
		header("Content-type: application/force-download");
		header(((is_integer(strpos($user_agent,"msie")))&&(is_integer(strpos($user_agent, "win"))))?"Content-Disposition:filename=\"$filename\"":"Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Description: Download"); 
		
		ob_clean();
		flush();
		readfile($fn);
	}
	else {
		header("HTTP/1.0 404 Not Found");
	}
	exit;
}

if( isset( $_GET['file' ] ) && isset( $_GET['name'] ) )
{
	dl_file ($_GET["file"],$_GET["name"]);
}
?>
