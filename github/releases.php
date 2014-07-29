<?php

$object='releases';

// Max number of elments to display
$max=1;

// JSON api URL
$json_url = 'https://api.github.com/repos/LMMS/lmms/' . $object;

// JSON cache file
$cache_file = $_SERVER['DOCUMENT_ROOT_HASH'] . '/../tmp/.json_github_' . $object;

// Use the cache version if it's less than 60 seconds old
if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60))) {
   $json = file_get_contents($cache_file);
} else {
   $json = file_get_contents_curl($json_url);
   file_put_contents($cache_file, $json, LOCK_EX);
}

// decode json data
$obj = json_decode($json);

$count = 0;
// loop through items and echo
foreach($obj as $item) {
   echo '';
   foreach($item->assets as $asset) {
      echo '<a style="margin-bottom: 3px;" class="btn btn-sm btn-success" href="' . $asset->browser_download_url . '">' .
	$asset->name . '&nbsp; <span class="badge">';
      echo $asset->download_count . '</span></a><br>'; 
   }
   echo '';
   $count++;
   if ($count == $max) { 
      echo '<a style="float:right;" href="' . $item->html_url . '">release notes</a>';
      break;
   }

}

// file_get_contents() won't work.  Use curl instead.
function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.x (linux)');
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

?>

