<?php

/*
 * A JSON utility class for parsing and caching JSON data.
 *
 * Author:
 *		Tres Finocchiaro 2014-08-05
 *		c/o LMMS Development Team
 *		http://lmms.io
 * License:
 * 		GPL 2+
 * Works with:
 * 		- GitHub API JSON formatted data
 *		- Google API JSON formatted data
 * NOT yet working:
 *		- Facebook API JSON formatted data
 *
 * Note:
 *		Google REQUIRES API approval **AND** white-listed IP address.
 *		This can be done by logging into the following site as lmms.service:
 *		https://console.developers.google.com/project/apps~lmms-api/apiui/credential
 *		and clicking "Edit Allowed IPs".  There should be a GOOGLE_KEY file
 *		located in $secrets_dir.  Alternately, you may place them in
 *		$alt_secrets_dir beside the cache files (if developing locally ONLY).
 *
 *		GitHub PREFERS using a secret for accessing their services but it
 *		is not mandated (rate limitations are more lenient when using a
 *		secret.  There should be GITHUB_CLIENT_ID **AND** GITHUB_CLIENT_SECRET
 * 		files located in $secrets_dir.  Alternately, you may place them in
 *		$alt_secrets_dir beside the cache files (if developing locally ONLY).
 * Usage:
 *		include_once('json_common.php');
 *
 *		// GitHub
 *		$obj = get_json_data('github', 'releases');
 *
 *		// Google
 *		$obj = get_json_data('google', 'activities', '?maxResults=25');
 */

/*
 * Maximum age, in minutes before refreshing the cache
 */
$max_age = filter_input(INPUT_GET, 'max_age');
if (!isset($max_age)) {
	$max_age = 120;
}



/*
 * Local JSON cache directory on webserver
 * This should always end in a forward slash
 */
$cache_dir = $_SERVER["DOCUMENT_ROOT"] . '/../tmp/';

/*
 * The directory on the server which stores the json secrets.
 * This should always end in a forward slash.
 */
$secrets_dir = '/home/deploy/secrets/';
$alt_secrets_dir = $cache_dir;

/*
 * Remote JSON api URL, this should always end in a forward slash
 * we will append more to this URL later.
 */
$github_api_url = 'https://api.github.com/repos/'; // . $repo . $object . $params;
$google_api_url = 'https://www.googleapis.com/plus/v1/people/';
$facebook_api_url = 'https://www.facebook.com/feeds/page.php'; // ?id=435131566543039&format=json
$soundcloud_api_url = 'https://api.soundcloud.com/groups/'; // . $repo . '.json'
$youtube_api_url = 'https://www.googleapis.com/youtube/v3/'; // ?part=snippet&maxResults=25&channelId=UCPnLp1llm71LkwsXP23cXzQ&key={YOUR_API_KEY}

/*
 * The JSON unique ID.  This may be a project name or a userid, depending
 * on the service.  i.e.
 *	$github_id = "lmms";
 *  $google_id = "113001340835122723950";
 */
$github_id = 'lmms';
$google_id = '113001340835122723950';
$facebook_id = '435131566543039';
$soundcloud_id = '8574';
$youtube_id = 'UCzGxut7RTCfln1ym1Q-sXkQ';

/*
 * Local JSON cache file name prefix.  Eventually this file will be created and
 * stored in $cache_dir above. This should usually end in an underscore.
 */
$github_cache_file = '.json_github_'; 			// . $repo . $object;
$google_cache_file = '.json_google_'; 			// . $user_id;
$facebook_cache_file = '.json_facebook_'; 		// . $user_id;
$soundcloud_cache_file = '.json_soundcloud_';	// . $object
$youtube_cache_file = '.json_youtube_';	// . $object

/*
 * Returns the JSON decoded object from the respective JSON service/API.
 * If the cached data is newer than $max_age or if the URL is temporarily
 * unavailable, it will attempt to return data from the last good cache.
 * Parameters:
 *	$service:  	Used to switch API providers.
 *		Example:  	"google", "github", "facebook", "soundcloud"
 *	$object: 	Used to switch between specific service components.
 *      Example: 	(github) 	"releases", "issues"
 *					(google)	"activities"
 *                  (soundcloud)"tracks", "members",
 *	$params:	Normally used to filter the results
 *		Example:	(github)	"?state=open"
 *					(google) 	"?maxResults=25
 *	$repo:		Overrides the default repository for information
 *		Example:	(github)	"tfino", "diizy", "Lukas-W"
 *					(google)	"113001340835122723950"
 *                  (soundcloud) "5532", "8574" (search the embed player code)
 */
function get_json_data($service, $object = NULL, $params = '', $repo = NULL) {
	$service_url = $GLOBALS[$service . '_api_url'];
	$service_id = $GLOBALS[$service . '_id'];
	$cache_file = $GLOBALS[$service . '_cache_file'];

	global $cache_dir;

	// Attempt to make the cache directory if it doesn't already exist
	if (!file_exists($cache_dir)) {
		mkdir($cache_dir);
	}

	// Local 'tmp' cache file on the webserver, preferably out of public reach, i.e.
	// htdocs/tmp/.json_github_lmms_releases.
	$tmp_suffix = ($repo ? $repo : $service_id) . ($object ? '_' . $object : '');
	$tmp_suffix = str_replace('/', '', str_replace('.', '', str_replace('__', '_', $tmp_suffix)));
	
	// For "resolve" requests, hash the track URL for cache filename
	if ($service == 'soundcloud' && $params && strpos($params, '://') !== false) {
		$tmp_suffix = md5($params) . $tmp_suffix;
	}
	
	$tmp_cache = $cache_dir . $cache_file . $tmp_suffix;

	// If the repository isn't specified, assume it's the same as the project name and build accordingly
	// i.e. "https://api.github.com/repos/lmms/lmms/releases?param=value"
	// i.e. "https://www.googleapis.com/plus/v1/people/113001340835122723950/activities/public?maxResults=25
	switch ($service) {
		case 'youtube' :
			$full_api = $service_url . ($object ? $object : 'playlists' ) . '?channelId=' . ($repo ? $repo : $service_id) . $params;
			break;
		case 'soundcloud' :
			$full_api = $service_url . ($repo ? $repo : $service_id) . '/' . ($object ? $object : 'tracks' ) . '.json' . $params;
			break;
		case 'facebook' :
			$full_api = $service_url . '?id=' . ($repo ? $repo : $service_id) . '&format=json' . $params;
			break;
		case 'google' :
			$full_api = $service_url . ($repo ? $repo : $service_id) . '/' . $object . '/public/' . $params;
			break;
		case 'github' :
		default:
			$full_api = $service_url . ($repo ? $repo : $service_id) . '/' . $service_id . '/' . $object . $params;
	}

	$using_url = false;

	if (cache_expired($tmp_cache)) {
		$json = file_get_contents_curl($full_api, $service);
		$using_url = true;
	} else {
		$json = file_get_contents($tmp_cache);
	}
	$obj = json_decode($json);


	/*
	* If there's valid JSON data, AND it came from the web cache it
	* If not, fall back to the previous cache
	*/
	if (has_children($obj, $service)) {
		if ($using_url) {
			@file_put_contents($tmp_cache, $json, LOCK_EX);
		}
		return $obj;
	} else {
		$json = file_get_contents($tmp_cache);
		return json_decode($json);
	}
}

/*
 * JSON Psuedo-code equivelant for "is_empty" or "is_array"
 * Needs some special considerations because JSON 404s
 * are often returned as perfectly valid data arrays.
 * Furthermore, the validity of the returned data differs
 * greatly between service providers.
 */
function has_children($obj, $service) {
	switch ($service) {
		case 'soundcloud' :
			return (count($obj) == 1 && $obj->user_id || count($obj) > 1 && $obj[0]->user_id);
		case 'facebook' :
			return (count($obj) > 0 && $obj->entries);
		case 'youtube':
		case 'google' :
			return (count($obj) > 0 && $obj->items);
		case 'github' :
		default :
			return (count($obj) > 0 && $obj[0]->url);
	}
	return false;
}

/*
 * Exposes the user_id defined in global namespace above to
 * other php files
 */
function get_json_id($service) {
	return $GLOBALS[$service . '_id'];
}


/*
 * Returns true if the requested file is older than $max_age (i.e. 1 minute)
 */
function cache_expired($cache) {
	global $max_age;
	if (file_exists($cache)) {
		if (filemtime($cache) > (time() - ($max_age * 60 ))) {
			return false;
		}
	}
	return true;
}

/*
 * For security reasons, file_get_contents() won't work for remote URLs
 * Use curl instead.
 *
 * Furthermore, GitHub puts lower rate limits on CURL requests so we'll
 * authenticate with a dedicated service account.
 */
function file_get_contents_curl($url, $service) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.x (linux)');
	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url . get_secrets($service, $url));
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

	// Skip SSL checks for localhost clients as Trusted CAs often aren't
	// installed into CURL on developer's PCs
	if (in_array($_SERVER["REMOTE_ADDR"], array ("127.0.0.1", "::1"))) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}

	$data = curl_exec_follow($ch);
	curl_close($ch);

	return $data;
}

/*
 * Returns the secrets in URL format, taking into consideration
 * the pre-existence of a '?' in the URL
 * i.e. ?client_id=tresf&client_secret=top_secret
 */
function get_secrets($service, $url) {
	// Check for '?' within URL and switch to '&' as needed
	// i.e. http://mydomain/myservice?param1=val1&param2=val2
	$delim = (strpos($url, '?') !== FALSE) ? '&' : '?';

	switch ($service) {
		case 'facebook' :
			return '';
		case 'soundcloud' :
			$key=get_base64_secret('SOUNDCLOUD_CLIENT_ID');
			return $key ? $delim . 'client_id=' . $key : '';
		case 'youtube':
		case 'google' :
			$key=get_base64_secret('GOOGLE_KEY');
			return $key ? $delim . 'key=' . $key : '';
			break;
		case 'github' :
		default:
			$client_id=get_base64_secret('GITHUB_CLIENT_ID');
			$client_secret=get_base64_secret('GITHUB_CLIENT_SECRET');
			return ($client_id ? $delim . 'client_id=' . $client_id : '') .
				($client_secret ? '&client_secret=' . $client_secret : '');
	}
}

/*
 * Attempts to read the encoded secret from the file system.
 * The secret must be the only content in the file and must be
 * encoded in base64 format
 */
function get_base64_secret($file) {
	global $secrets_dir, $alt_secrets_dir;
	$base64 = @file_get_contents($secrets_dir . $file);
	if (!$base64) {
		$base64 = @file_get_contents($alt_secrets_dir . $file);
	}
	return base64_decode($base64);
}

/*
 * Trims message to the specified character length, or 500 if none is provided.
 */
function trim_feed($message, $hyperlink, $max_length = 500) {
	if (strlen($message) > $max_length) {
			return explode('\n', wordwrap($message, $max_length, '\n', true))[0] . ' <a class="ellipsis" target="_blank" href="' . $hyperlink . '">...</a>';
			//return substr($message, 0, $max_length) . ' <a class="ellipsis" target="_blank" href="' . $hyperlink . '">...</a>';
	}
	return $message;
}

/*
 * Creates an html row, i.e: '<tr><td></td></tr>' wrapped around feed content
 */
function create_row($service, $title, $href, $message, $author = NULL, $author_href = NULL, $date = NULL, $thumbnail = NULL, $id = NULL) {
	// Replace blank titles with some default presets
	if (!isset($title) || trim($title) == '') {
		$title = get_alternate_title($service);
	}

	$id = (isset($id) ? 'id="' . $id  . '" ' : '');

	echo '<tr><td><div ' . $id . 'class="row ' . $service . '-row"><a target="_blank" href="' . $href . '"><h4><strong>';
	if (isset($thumbnail)) {
		echo '<img class="img-thumbnail ' . $service . '-thumb" src="' . $thumbnail . '"/>';
	}

	if ($service == 'youtube') {
		echo '<div class="' . $service . '-thumb-overlay"><span class="fa-3x fa fa-play"></span></div>';
	}

	echo get_icon($service) . ' ' . $title . '</strong></h4></a>';

	echo $message;
	// Format and concat a pretty timestamp

	if (isset($author)) {
		echo '</div><small class="feed-small">Posted by: <a href="' . $author_href . '">' . $author . '</a> at ' .
			date("D, d M Y h:ia ", strtotime($date)) . '(GMT ' . sprintf('%+d', date('O')*1/100) . ')</small>';
	}
	echo '</td></tr>';
}

/*
 * Returns a font-awesome icon string associated with the specified service
 * $service = 'facebook';
 */
function get_icon($service, $invert = false, $extra_class = '') {
	$class = '';
	switch ($service) {
		case 'forums':
			$class .= 'fa-comments';
			break;
		case 'facebook':
			$class .= ($invert ? 'fa-facebook' : 'fa-facebook-square');
			break;
		case 'soundcloud':
			$class .= 'fa-soundcloud';
			break;
		case 'google+':
			$class .= 'fa-google-plus';
			break;
		case 'github':
			$class .= 'fa-github';
			break;
		case 'youtube':
			$class .= 'fa-youtube';
			break;
		default:
			$class .= 'fa-book';
	}
	return '<span class="' . trim('fa ' . $class . ' ' .  $extra_class) . '"></span>';
}

function get_alternate_title($service) {
	switch ($service) {
		case 'soundcloud':
			return 'Community Track';
		case 'forums':
			return 'Forum Topic';
		case 'github':
			return 'LMMS Issue';
		case 'google+':
		case 'facebook':
		default:
			return 'LMMS Announcement';
	}
}

/*
 * Checks an href URL for most well-known image formats
 * Also has a patch for facebook images which don't use traditional image file extensions
 * but rather have the file extension as part of the element innerText
 */
function is_image($a_tag) {
	$image_exts = explode(',', 'png,jpg,jpeg,gif,bmp,tif,tiff,svg');
	foreach($image_exts as $ext) {
		if (str_endswith($a_tag->href, '.' . trim($ext), true) || str_contains($a_tag->innertext, '.' . trim($ext) . '?', false)) {
			return true;
		}
	}
	return false;
}

/*
 *
 */
function scale_image($url, $width) {
	ini_set('user_agent', 'gd/2.x (linux)');

	$image = NULL;
	try {
		switch (strtolower(pathinfo($url, PATHINFO_EXTENSION))) {
			case 'jpg':
			case 'jpeg':
				$image = @imagecreatefromjpeg($url); break;
			case 'gif':
				$image = @imagecreatefromgif($url); break;
			case 'bmp':
				$image = @imagecreatefromwbmp($url); break;
			case 'png':
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

	return 'data:image/png;base64,' . base64_encode($i). '"';
}

/*
 * Work-around for safe_mode server configs that prevent following
 * redirects
 * Author: slopjong, 2012-03-31
 */
function curl_exec_follow($ch, &$maxredirect = 5) {
	curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.x (linux)');
	$mr = intval($maxredirect);
	
	if (filter_var(ini_get(‘open_basedir’), FILTER_VALIDATE_BOOLEAN) === false 
      && filter_var(ini_get(‘safe_mode’), FILTER_VALIDATE_BOOLEAN) === false) {
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
		curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	} else {
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		if ($mr > 0) {
			$original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$newurl = $original_url;
			$rch = curl_copy_handle($ch);

			curl_setopt($rch, CURLOPT_HEADER, true);
			curl_setopt($rch, CURLOPT_NOBODY, true);
			curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
			do {
				curl_setopt($rch, CURLOPT_URL, $newurl);
				$header = curl_exec($rch);
				if (curl_errno($rch)) {
					$code = 0;
				} else {
					$code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
					if ($code == 301 || $code == 302) {
						preg_match('/Location:(.*?)\n/i', $header, $matches);
						$newurl = trim(array_pop($matches));
						
						// if no scheme is present then the new url is a
						// relative path and thus needs some extra care
						if(!preg_match("/^https?:/i", $newurl)){
						  $newurl = $original_url . $newurl;
						}   
					} else {
						$code = 0;
					}
				}
			} while ($code && --$mr);
			curl_close($rch);
			if (!$mr) {
				if ($maxredirect === null) 
					trigger_error('Too many redirects.', E_USER_WARNING);
				else 
					$maxredirect = 0;
					return false;
			}
			curl_setopt($ch, CURLOPT_URL, $newurl);
		}
	}
	return curl_exec($ch);
}

?>
