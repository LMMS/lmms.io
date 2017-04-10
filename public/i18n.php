<?php
$langs = array();
$locale = 'en_US.utf-8'; // default
$weight = 0.0;
//snippet from http://www.thefutureoftheweb.com/blog/use-accept-language-header
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// break up string into pieces (languages and q factors)
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
		if (count($lang_parse[1])) {
				// create a list like "en" => 0.8
				$langs = array_combine($lang_parse[1], $lang_parse[4]);
				// set default to 1 for any without q factor
				foreach ($langs as $lang => $val) {
						if ($val === '') {
								$val = 1;
						}
						if ($val > $weight) {
								$locale = $lang;
								$weight = $val;
						}
				}
		}
}
//end of snippet
function set_language($lang_pair) {
	$locale = str_replace('-', '_', $lang_pair) . '.utf-8';
	putenv("LANGUAGE=$locale"); // Workaround for ISO language code given by browser
	putenv("LC_ALL=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages", "./locale");
	textdomain("messages");
}

set_language($locale);
