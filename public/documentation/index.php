<?php include('../header.php'); ?>
<div class="jumbotron jumbo">
	<div class="container">
		<h1 class="jumbo">Documentation</h1>
	</div>
</div>
<?php
begin_content();

require_once('../../lib/RemWiki/RemWiki.php');

if (array_key_exists('page', $_GET)) {
	$page = $_GET['page'];
} else {
	$page = 'Main_Page';
}

$wiki = new RemWiki\RemWiki('http://lmms.sourceforge.net/wiki/');
$json = $wiki->parse($page);

echo '<h1>' . $json->displaytitle . '</h1>';
echo $json->text->{'*'};

include('../footer.php');
