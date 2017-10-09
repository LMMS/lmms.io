<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/FilesystemCache.php');
/**
 * LMMS i18n framework
 */
class LMMSI18N
{

	private $regions;
	private $locale;

	function __construct()
	{
		$this->scanLocales();
		$this->locale = 'en_US'; // default
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			// use php native locale detection (requires php 5.3+ w/ php-intl installed)
			$this->locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		}
		//dirty overrides
		if (isset($_COOKIE['lng'])) {
			$this->locale = $_COOKIE['lng'];
		}
		if (isset($_GET['lang'])) {
			$this->locale = $_GET['lang'];
			setcookie('lng', $this->locale, time() + 7776000, '/'); // 90 days
		}
		$this->setLanguage($this->locale);
	}

	/* Convert locale names to region neutral names */
	function cutOffLocale($lk) {
		$excluded = ['zh_CN', 'zh_TW'];
		if (in_array($lk, $excluded)) {
			return $lk;
		}
		return Locale::getPrimaryLanguage($lk);
	}

	function scanLocales() {
		$pool = new \LMMS\FilesystemCache('/tmp/github-markdown-cache');
		if ($pool->has('lks')) {
			$this->regions = $pool->get('lks');
		} else {
			$this->regions = array();
			$localeDir = glob(dirname(__FILE__) . '/locale/*', GLOB_ONLYDIR);
			array_push($localeDir, '/en_US');
			foreach ($localeDir as $lk) {
		    $lk = basename($lk);
				$this->regions[$lk] = Locale::getDisplayName($this->cutOffLocale($lk), $lk);
			}
			$pool->set('lks', $this->regions);
		}
	}

	function setLanguage($lang_pair) {
		$locale = str_replace('-', '_', $lang_pair); // Workaround for ISO language code given by browser
		putenv("LANGUAGE=$locale");
		putenv("LANG=$locale");  // plain old gettext will need this
		putenv("LC_ALL=$locale.utf-8");
		setlocale(LC_ALL, $locale . '.utf-8');
		bindtextdomain("messages", "./locale");
		textdomain("messages");
		bind_textdomain_codeset("messages", 'UTF-8');
	}

	function getLanguage() {
		return $this->locale;
	}

	function getSupportedLocales() {
		return $this->regions;
	}

	function langDropdown() {
		$availableRegions = array();
		$pageURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		foreach ($this->regions as $lk => $value) {
			array_push($availableRegions, [null, $value, "$pageURI?lang=$lk"]);
		}
		//language dropdown is right aligned
		return [$this->regions[$this->locale], NULL, $availableRegions, true];
	}
}
