<?php

include "inc/config.inc.php";
include "inc/mysql.inc.php";
echo "here\n";

$cats = get_categories_for_ext( ".png" );
   connectdb();
$ext = ".png";
$result = mysql_query( 'SELECT * FROM filetypes WHERE extension LIKE \''.mysql_real_escape_string( $ext ).'\'' );
 while( $object = mysql_fetch_object( $result ) )
        {
		var_dump($object);
echo "<br/>";
        }
        mysql_free_result( $result );

var_dump($cats);

?>

