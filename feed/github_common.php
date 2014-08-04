<?php

/*
 * Maximum age, in minutes before refreshing the cache
 */
$max_age = 1;

/*
 * Remote JSON api URL, this should always end in a forward slash
 */ 
$api_url = 'https://api.github.com/repos/'; // . $repo . $object . $params;

/*
 * The project name, i.e 'lmms'
 */
$project = 'lmms';

/*
 * Local JSON cache directory
 */
$cache_dir = get_document_root() . '/../tmp/';

/*
 * Local JSON cache file name prefix, stored in $cache_dir above
 */
$cache_file = '.json_github_'; // . $object;

/*
 * Returns the JSON decoded object from the respective github URL
 * If the cached data is newer than $max_age or if the URL is temporarily
 * unavailable, it will attempt to return data from the last good cache.
 */
function get_github_data($object, $params, $repo) {
	global $project, $api_url, $cache_dir, $cache_file;
	
	// Attempt to make the cache directory if it doesn't already exist
	if (!file_exists($cache_dir)) {
		mkdir($cache_dir);
	}
	
	// Local 'tmp' cache file on the webserver, preferably out of public reach, i.e.
	// htdocs/tmp/.json_github_lmms_releases
	$tmp_cache = $cache_dir . $cache_file . (@$repo ? $repo : $project) . '_' . $object;
	
	// If the repository isn't specified, assume it's the same as the project name and build accordingly
	// i.e. "https://api.github.com/repos/lmms/lmms/releases?param=value"
	$full_api = $api_url . (@$repo ? $repo : $project) . '/' . $project . '/' . $object . $params;
	
	$using_url = false;
	if (cache_expired($tmp_cache)) {
		$json = file_get_contents_curl($full_api);
		$using_url = true;
	} else {
		$json = file_get_contents($tmp_cache);
	}
	$obj = json_decode($json);


	/*
	* If there's valid JSON data, AND it came from the web cache it
	* If not, fall back to the previous cache
	*/
	if (count($obj) > 0 && $obj[0]->url) {
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
 * Returns true if the requested file is older than $max_age (i.e. 1 minute)
 */
function cache_expired($cache) {
	global $max_age;
	if (file_exists($cache)) {
		if (filemtime($cache) > time() - ($max_age * 60 )) {
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
function file_get_contents_curl($url) {
	$client_id=base64_decode(@file_get_contents('/home/lmms/GITHUB_CLIENT_ID'));
	$client_secret=base64_decode(@file_get_contents('/home/lmms/GITHUB_CLIENT_SECRET'));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.x (linux)');
	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url . '?client_id=' . $client_id . '&client_secret=' . $client_secret);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	
	// Skip SSL checks for localhost clients as Trusted CAs often aren't
	// installed into CURL on developer's PCs
	if (in_array($_SERVER["REMOTE_ADDR"], array ("127.0.0.1", "::1"))) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	
	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
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
