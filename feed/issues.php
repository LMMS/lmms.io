<?php

$object='issues';
$params='?state=open';
$max=20;

// JSON api URL
$json_url = 'https://api.github.com/repos/LMMS/lmms/' . $object . $params;

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


// loop through items and echo
$count = 0;
foreach($obj as $item) {

      echo '<a class="btn btn-default btn-xs" target="new" href="' . $item->html_url . '">#' . $item->number . '</a>&nbsp;';
      echo '<a target="new" href="' . $item->html_url . '">' . $item->title. '</a><br>';

      $count++;
      if ($count == $max) { break; }
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

