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
$max_age = 15;


/*
 * Local JSON cache directory on webserver
 * This should always end in a forward slash
 */
$cache_dir = get_document_root() . '/../tmp/';

/*
 * The directory on the server which stores the json secrets.
 * This should always end in a forward slash.
 */
$secrets_dir = '/home/lmms/';
$alt_secrets_dir = $cache_dir;

/*
 * Remote JSON api URL, this should always end in a forward slash
 * we will append more to this URL later.
 */ 
$github_api_url = 'https://api.github.com/repos/'; // . $repo . $object . $params;
$google_api_url = 'https://www.googleapis.com/plus/v1/people/';
$facebook_api_url = 'https://www.facebook.com/feeds/page.php'; // ?id=435131566543039&format=json
$soundcloud_api_url = 'https://api.soundcloud.com/groups/'; // . $repo . '.json'

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

/*
 * Local JSON cache file name prefix.  Eventually this file will be created and
 * stored in $cache_dir above. This should usually end in an underscore.
 */
$github_cache_file = '.json_github_'; 			// . $repo . $object;
$google_cache_file = '.json_google_'; 			// . $user_id;
$facebook_cache_file = '.json_facebook_'; 		// . $user_id;
$soundcloud_cache_file = '.json_soundcloud_';	// . $object

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
	// htdocs/tmp/.json_github_lmms_releases
	$tmp_cache = $cache_dir . $cache_file . ($repo ? $repo : $service_id) . ($object ? '_' . $object : '');
	
	// If the repository isn't specified, assume it's the same as the project name and build accordingly
	// i.e. "https://api.github.com/repos/lmms/lmms/releases?param=value"
	// i.e. "https://www.googleapis.com/plus/v1/people/113001340835122723950/activities/public?maxResults=25
	switch ($service) {
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
			return (count($obj) > 0); // && $obj[0]->user_id);
		case 'facebook' :
			return (count($obj) > 0 && $obj->entries);
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
	
	echo '<p>' . $url . get_secrets($service, $url) . '</p>';

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	
	// Skip SSL checks for localhost clients as Trusted CAs often aren't
	// installed into CURL on developer's PCs
	//if (in_array($_SERVER["REMOTE_ADDR"], array ("127.0.0.1", "::1"))) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	//}
	
	$data = curl_exec($ch);
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
	echo '<p>base64:' . $base64 . '</p>';
	echo '<p>secrets file:' . $secrets_dir . $file . '</p>';
	if (!$base64) {
		$base64 = @file_get_contents($alt_secrets_dir . $file);
		echo '<p>base64:' . $base64 . '</p>';
		echo '<p>alt_secrets file:' . $alt_secrets_dir . $file . '</p>';
	}
	return base64_decode($base64);
}

/*
 * Checks for a special "DOCUMENT_ROOT_HASH" global first
 * and fall-back to the standard "DOCUMENT_ROOT" if unavailable
 */
function get_document_root() {
	// Prefixed with '@' to prevent php warnings
	$retval =@$_SERVER["DOCUMENT_ROOT_HASH"];
	return ($retval ? $retval : $_SERVER["DOCUMENT_ROOT"]);
}

?>
