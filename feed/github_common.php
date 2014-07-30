<?php

/*
 * Maximum age, in minutes before refreshing the cache
 */
$max_age = 1;

/*
 * Remote JSON api URL
 */
$json_url = 'https://api.github.com/repos/LMMS/lmms/'; //. $object . $params;

/*
 * Local JSON cache file
 */
$cache_file = get_document_root() . '/../tmp/.json_github_'; // . $object;

/*
 * Returns the JSON decoded object from the respective github URL
 * If the cached data is newer than $max_age or if the URL is temporarily
 * unavailable, it will attempt to return data from the last good cache.
 */
function get_github_data($object, $params) {
   global $json_url;
   global $cache_file;

   $using_url = false;
   if (cache_expired($cache_file . $object)) {
	  $json = file_get_contents_curl($json_url . $object . $params);
      $using_url = true;
   } else {
      $json = file_get_contents($cache_file . $object);
   }
   $obj = json_decode($json);


   /*
    * If there's valid JSON data, AND it came from the web cache it
    * If not, fall back to the previous cache
    */
   if (count($obj) > 1) {
      if ($using_url) {
         file_put_contents($cache_file . $object, $json, LOCK_EX);
      }
      return $obj;
   } else {
      $json = file_get_contents($cache_file . $object);
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
