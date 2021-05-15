<?php

function str_startswith($haystack, $needle, $ignorecase = FALSE) {
	if ($ignorecase) {
		return $needle === "" || strpos(strtolower($haystack), strtolower($needle)) === 0;
	}
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function str_endswith($haystack, $needle, $ignorecase = FALSE) {
	if ($ignorecase) {
		return $needle === "" || substr(strtolower($haystack), -strlen($needle)) === strtolower($needle);
	}
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/*
 * Helper function to replace first occurance
 */
function str_replace_first($find, $replace, $subject) {
	return implode($replace, explode($find, $subject, 2));
}

/*
 * Determines if a file is an image based on its file extension
 */
function is_image($file_path) {
	$images = explode(',', '.jpg,.jpeg,.gif,.png,.bmp,.tiff,.svg');
	return in_array(parse_extension($file_path), $images);
}

/*
 * Determines if a URL is an LSP image
 * If so, returns the lsp id of the image which fixes
 * thumbnail issues caused by LSP hot-linking done on the forums
 */
 function is_lsp_image($file_path) {
	if (!is_image($file_path) || explode("download_file.php", $file_path) < 2) {
		return false;
	}
	$parsed = parse_url($file_path);
	if (isset($parsed['query'])) {
		$query = array();
		parse_str($parsed['query'], $query);
		return $query['file'];
	}
	return false;
 }

/*
 * Using GD, scales the supplied image server-side to create a 
 * proportional thumbnail in base64 image format
 */
function scale_image($url, $width, $extension = null) {
	if ($extension == null) {
		$extension = parse_extension($url);
	}
	
	$lsp_image = is_lsp_image($url);
	if ($lsp_image !== false) {
		global $DATA_DIR;
		include_once('lsp/dbo.php');
		$url = "$DATA_DIR$lsp_image";
	}
	
	ini_set('user_agent', 'gd/2.x (linux)');
	$image = NULL;
	try {
		switch ($extension) {
			case '.jpg':
			case '.jpeg':
				$image = @imagecreatefromjpeg($url); break;
			case '.gif':
				$image = @imagecreatefromgif($url); break;
			case '.bmp':
				$image = @imagecreatefromwbmp($url); break;
			case '.png':
			default:
				$image = @imagecreatefrompng($url); break;
		}
	} catch (Exception $e) {
		return $url;
	}

	if ($image === false) {
		return $url;
	}

	$orig_width = imagesx($image);
	$orig_height = imagesy($image);

	if ($orig_width < $width) {
		return $url;
	}

	// Calc the new height
	$height = (($orig_height * $width) / $orig_width);

	// Create new image to display
	$new_image = imagecreatetruecolor($width, $height);
	imagealphablending($new_image, false);
	imagesavealpha($new_image, true);

	// Create new image with changed dimensions
	imagecopyresampled($new_image, $image,
		0, 0, 0, 0,
		$width, $height,
		$orig_width, $orig_height);

	// Capture object to memory
	ob_start();
	//header( "Content-type: image/jpeg" );
	imagepng($new_image);
	imagedestroy($new_image);
	$i = ob_get_clean();

	return 'data:image/png;base64,' . base64_encode($i);
}

/*
 * Returns the file extension (including the dot), taking into consideration the double
 * extensions used by linux archives, i.e. .tar.gz
 */
function parse_extension($file_path) {
	if (strtolower(pathinfo(pathinfo($file_path, PATHINFO_FILENAME), PATHINFO_EXTENSION)) == 'tar') {
		return strtolower('.tar.' . pathinfo($file_path, PATHINFO_EXTENSION));
	} else {
		return strtolower('.' . pathinfo($file_path, PATHINFO_EXTENSION));
	}
}

/*
 * Embed youtube iframe based on URL 
 */
function youtube_iframe($url, $width = 300, $height = -1, $opts = '') {
	if ($height == -1) {
		$height = intval((9 * $width) / 16);
	}
	// Snag the url part from the full URL
	if (strpos($url, "http://") === 0 || strpos($url, "https://") === 0) {
		$parts = parse_url($url);
		if (isset($parts['query'])) {
			parse_str($parts['query'], $query);
			if (isset($query['v']) ){
				$url = $query['v'];
			}
		} else {
			$url = $parts['path'];
		}
	}
	
	$html = '<iframe width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . 
		$url . $opts . '" frameborder="0" allowfullscreen></iframe>';
		
	return $html;
}

?>
