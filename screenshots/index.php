<?php include('../header.php'); ?>
<div class="page-header">
	<h1>Screenshots</h1>
</div>
<div style="text-align: center; overflow: hidden;" class="row visible-lg">
    <div class="col-lg-4">
		<h4>Song editor</h4>
		<?php make_reflection('/img/ss_song_editor.png', NULL, "white"); ?>
	</div>
	<div class="col-lg-4">
		<h4>Automation editor</h4>
		<?php make_reflection('/img/ss_automation.png', NULL, "white"); ?>
	</div>
	<div class="col-lg-4">
		<h4>B&amp;B editor and the FX-mixer</h4>
		<?php make_reflection('/img/ss_bb_mixer.png', NULL, "white"); ?>
	</div>
    <div class="col-lg-4">
        <h4>Piano Roll editor</h4>
        <?php make_reflection('/img/ss_piano_roll.png', NULL, "white"); ?>
    </div>
    <div class="col-lg-4">
        <h4>Built-in instruments</h4>
        <?php make_reflection('/img/ss_plugins.png', NULL, "white"); ?>
    </div>
    <div class="col-lg-4">
        <h4>Two VSTi running via Vestige</h4>
        <?php make_reflection('/img/ss_vst.png', NULL, "white"); ?>
    </div>
</div>


<?php ?>
<?php include('../footer.php'); ?>
<?php

function make_reflection($image_path, $thumbnail_path = NULL, $blackorwhite = 'black', $class = '') {
	// If no thumbnail is supplied, try 'th_' . $image_path
	if (!$thumbnail_path) {
		$pieces = explode('/', $image_path);
		$pieces[count($pieces) -1] = 'th_' . $pieces[count($pieces) -1];
		$thumbnail_path = implode('/', $pieces);
	}
	echo '<div id="reflect-' . $blackorwhite . '" class="image-block ' . $class . '">';
	echo '<a target="_blank" href="' . $image_path . '"><img src="' . $thumbnail_path . '" alt="" /></a>';
	echo '<div class="reflection">';
	echo '	<img src="' . $thumbnail_path . '" alt="" />';
	echo '	<div class="overlay"></div>';
	echo '</div>';
	echo '</div>';
}

?>
