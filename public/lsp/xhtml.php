<?php
$DOCTYPE = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
class form {
	function form ($action) { echo '<form enctype="multipart/form-data" action="' . $action . '" method="post">'; }
	function close () { echo '</form>'; } 
}
?>

