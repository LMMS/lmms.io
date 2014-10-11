<?php
require_once('lsp_dl.php');
if( $_GET['download'] == 'index' )
{
	$req = "SELECT files.filename AS fname, ".
		"files.hash AS hash, ".
		"categories.name AS catname, ".
		"subcategories.name AS subcatname, ".
		"files.size AS size, ".
		"files.update_date AS date, ".
		"users.login AS author ".
		"FROM files ";
 	$req .= "INNER JOIN categories ON categories.id=files.category ";
 	$req .= "INNER JOIN subcategories ON subcategories.id=files.subcategory ";
 	$req .= "INNER JOIN users ON users.id=files.user_id ";
	$req .= "ORDER BY files.id";
	connectdb();
	$result = mysql_query ($req);

	header( 'Content-Type: text/xml' );
	header( 'Content-Description: LMMS WebResources Index' );
	echo "<?xml version=\"1.0\"?>\n";
	echo "<!DOCTYPE lmms-webresources-index>\n";
	echo "<webresources>\n";
	while ($object = mysql_fetch_object ($result))
	{
		echo "<file>".
			"<name>".htmlentities($object->fname, ENT_COMPAT, 'UTF-8')."</name>".
			"<hash>".$object->hash."</hash>".
			"<size>".$object->size."</size>".
			"<date>".$object->date."</date>".
			"<author>".htmlentities($object->author, ENT_COMPAT, 'UTF-8')."</author>".
			"<dir>".htmlentities($object->catname."/".$object->subcatname, ENT_COMPAT, 'UTF-8')."</dir>".
			"</file>\n";
	}
	echo "</webresources>\n";
	flush();
}
else if( $_GET['download'] == 'resource' && isset( $_GET['id'] ) )
{
	$hash = $_GET['id'];
	$fid = get_object_by_id( 'files', $hash, 'id', 'hash' );
	$fname = get_object_by_id( 'files', $hash, 'filename', 'hash' );
	dl_file( $fid, $fname);
}
?>
