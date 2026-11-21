<?php
require_once('download_file.php');
require_once('dbo.php');
if (GET('download') == 'resource' && !GET_EMPTY('id')) {
	$hash = GET('id');
	$file_id = get_object_by_id('files', $hash, 'id', 'hash');
	$file_name = get_object_by_id('files', $hash, 'filename', 'hash');
	download_file($file_id, $file_name);
} else {
	header( 'Content-Type: text/xml' );
	header( 'Content-Description: LMMS WebResources Index' );
	echo '<?xml version="1.0"?>';
	echo '<!DOCTYPE lmms-webresources-index>';
	if ((GET('download') == 'index')) {
		echo '<webresources>';
		get_web_resources();
		echo '</webresources>';
	} else {
		echo '<error>Please contact the LMMS development team for API access</error>';
	}
	flush();
}
?>
