<?php
include_once('../header.php');
include_once('../utils.php');
?>
<div class="page-header">
	<h1>Screenshots</h1>
</div>
<div class="row text-center overflow-hidden">
<?php
	$ss_dir = '../img/screenshots/';
	$files = scandir($ss_dir);
	foreach($files as $file) {
		if (str_startswith($file, 'ss_', true) && str_endswith($file, '.png', true)) {
			echo '<div class="col-lg-4">';
			echo '<h4>' . humanize_title($file) . '</h4>';
			make_reflection($ss_dir . $file, NULL, "white");
			echo '</div>';
		}
	}
?>
</div>

<?php 

/*
 * Creates an english-readable title from a file name
 */
function humanize_title($filename) {
	$replacement = array(
		'ss' => '',
		'bb' => 'B&amp;B Editor',
		'mixer' => 'FX Mixer',
		'roll' => 'Roll Editor',
		'plugins' => 'Native Instruments',
		'automation' => 'Automation Editor',
		'vst' => 'VSTi Running via Vestige'
	);
	
	$title_split = explode('_', $filename);

	$found = false;
	foreach($title_split as &$item) {
		// Skip 01, 02, etc
		if (is_numeric($item)) {
			$item = '';
			continue;
		}
		// Substitute array reference with the text above
		if (str_contains($item, '.png', false)) {
			$item = str_replace('.png', '', $item);
		}
		
		if (array_key_exists($item, $replacement)) {
			$temp = $replacement[$item];
			$item = ($found ? ', ' : ' ') . $temp;
			$found = trim($temp) != '' ? true : false;
		} else {
			$item = ' ' . ucfirst($item);
		}
	}
	
	return trim(implode('', $title_split));
}
?>

<?php include('../footer.php'); ?>
