<?php
$DOCTYPE = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "https://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';



class form {
	public $colDiv = false;
	public $noAction = false;
	function form($action, $title = 'Form', $fa = '', $colDiv = false) {
		$this->colDiv = $colDiv;
		$this->noAction = ($action == null) ? true : false;
		// Wrap form in column div, unless specified (used for right-pane forms)
		if ($this->colDiv) { echo '<div class="col-md-9">'; }
		echo '<div class="panel panel-default"><div class="panel-heading">';
		echo '<h3 class="panel-title"><span class="fa ' . $fa . '"></span>&nbsp;' . $title . '</h3></div>';
		echo '<div class="panel-body">';
		if (!$this->noAction) {
			echo '<form enctype="multipart/form-data" action="' . $action . '" method="post">';
		}
	}
	function close() { 
		if ($this->noAction) {
			echo '</form>';
		}
		echo '</div></div>';
		if ($this->colDiv) { echo '</div>'; }
	}
}
?>

