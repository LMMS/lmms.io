<?php

namespace RemWiki;

require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\File;

/**
 * Helper class for getting rendered pages from a remote MediaWiki instance
 */
class RemWiki
{
	/**
	 * Set up the remote wiki.
	 *
	 * @param string $url URL of the wiki instance. $url/api.php should
	 * be present.
	 */
	public function __construct($url)
	{
		if (substr($url, -1) != '/') {
			$url = $url . '/';
		}
		$this->url = $url;

		$this->wikipath = parse_url($url, PHP_URL_PATH);
		if ($this->wikipath == false) {
			throw new Exception('Invalid wiki URL');
		}

		$adapter = new LocalAdapter('/tmp/doc', true);
		$this->fs = new Filesystem($adapter);
	}

	private function cacheFile($page)
	{
		return new File($page . '.html', $this->fs);
	}

	private function revFile($page)
	{
		return new File($page . '.rev', $this->fs);
	}

	private function requestRev($page)
	{
		$ch = curl_init($this->url.'api.php?format=json&action=query&prop=info&titles='.$page);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
		$response = curl_exec($ch);
		curl_close($ch);

		if ($response) {
			return reset(json_decode($response)->query->pages)->lastrevid;
		}
	}

	private function requestParse($page)
	{
		// Do CURL request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $this->url."api.php?format=json&action=parse&page=$page");
		$response = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($response);

		// Fix relative links in rendered HTML
		$html = $json->parse->text->{'*'};

		// Get the wiki's relative path on its server
		// e.g. 'http://lmms.sf.net/wiki/' -> '/wiki/'
		$path_escaped = preg_replace('/\//', '\/', $this->wikipath);

		// Fix links
		$html = preg_replace(
			[
				// Internal links to wiki pages
				'/"'.$path_escaped.'index.php\/(.+?)"/m',
				// Links to other resources like images
				'/"'.$path_escaped.'(.+?)"/m',
				// Thumbnails
				'/class="thumbimage"/m',
			],
			[
				'/documentation/$1',
				$this->url.'$1',
				'class="img-thumbnail"',
			],
			$html
		);

		$json->parse->text->{'*'} = $html;

		return $json->parse;
	}

	public function parse($page)
	{
		$revfile = $this->revFile($page);
		$cachefile = $this->cacheFile($page);

		if ($revfile->exists()) {
			$localrev = intval($revfile->getContent());

			// Don't check for newer revisions more often than every 5 minutes
			if ((time() - $revfile->getMtime()) < 60*5) {
				return json_decode($cachefile->getContent());
			} else {
				// Is there a newer remote revision?
				$remoterev = $this->requestRev($page);
				if ($remoterev == $localrev) {
					$revfile->setContent($remoterev);
					return json_decode($cachefile->getContent());
				}
			}
		} else {
			$remoterev = $this->requestRev($page);
		}
		$json = $this->requestParse($page);
		$cachefile->setContent(json_encode($json));
		$revfile->setContent($remoterev);

		return $json;
	}

	private $url;
	private $wikipath;
	private $ch;
	private $fs;
}
