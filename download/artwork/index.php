<?php include(__DIR__ . '/header.php'); ?>
<?php include(__DIR__ . '/feed/releases.php'); ?>
<div class="page-header">
	<h1>Download LMMS Artwork</h1>
</div>


<?php 

/* Usage:
 * 	create_artwork_item($artwork_name, 
 *		$img_path, $raw_path, 
 *		$rendered_path, 
 *		$author_name, 
 *		$author_link, 
 *		$modified_by);
 */
create_artwork_item('LMMS Logo', 
	'img/logo_md.png', 
	'http://raw.githubusercontent.com/LMMS/artwork/master/src/icon.svg', 
	'img/logo_lg.png', 
	'Martin Vacho', 
	'http://hdche.tumblr.com', 
	'LMMS Development Team');

create_artwork_item('LMMS Project Icon', 
	'img/project_md.png', 
	'http://raw.githubusercontent.com/LMMS/artwork/master/src/mmpz_file_icon.svg', 
	'img/project_lg.png', 
	'Ubuntu', 
	'https://wiki.ubuntu.com/Artwork/Incoming/Karmic/Humanity_Icons', 
	'LMMS Development Team');
	
 ?>

<?php 


/*
 * Creates a small summary box about an artwork item
 */
function create_artwork_item($artwork_name, $img_path, $raw_path, $rendered_path, $author_name = NULL, $author_link = NULL, $modified_by = NULL) {
	echo '<img style="padding-right: 10px; float:left;" src="' . $img_path . '">';
	echo '<h3>' . $artwork_name . '</h3>';
	
	if ($author_name) {
		echo '<p><strong>Author:</strong> <a href="' . $author_link . '">' . $author_name . '</a><br>';
	} else {
		echo '<p>';
	}
	
	if ($modified_by) {
		echo '<strong>Modified/Themed By: </strong>' . $modified_by . '</p>';
	}	else {
		echo '</p>';
	}
	
	$buttons = array ( $raw_path, $rendered_path );
	
	foreach ($buttons as $button) {
		if ($button) {
			$file_name = basename($button);
			$extension = pathinfo($button)['extension'];
			$description = get_file_description($extension);
			$description = $description ? ' <br><small>(' . $description . ')</small>' : '';
		
			echo '<a target="_blank" class="btn btn-default" href="' . $button . '" download><span class="fa fa-image"></span> ';
			echo $file_name . $description . '</a>&nbsp; ';
		}
	}
	
	echo '<br><br><hr><br><br>';
}

/*
 * Basic function for retrieving a file description based on extension:
 *    i.e. "svg" = "Scalable Vector Graphics"
 * but can be moved to a common php file and/or extended to other parts of the site as well
 */
function get_file_description($extension) {
	if (!$extension) {
		return;
	}
	switch (strtolower($extension)) {
		case 'svg' : return 'Scalable Vector Format';
		case 'xcf' : return 'Gimp Image Editor Format';
		case 'pdf' : return 'Portable Document Format';
		case 'psd' : return 'Adobe Photoshop Format';
		case 'png' : return 'PNG Format';
		case 'jpg' : 
		case 'jpeg': return 'JPEG Format';
		case 'bmp' : return 'Bitmap Format';
		case 'ico' : return 'Windows Icon Format';
		case 'icns': return 'Apple Icon Format';
		default:	return "Unknown File";
	}
}

?>

<?php include('footer.php'); ?>
