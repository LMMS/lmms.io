<?php

namespace RemWiki;


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
	}

	public function parse($page)
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
				// Internal inks to wiki pages
				'/"'.$path_escaped.'index.php\/(.+?)"/m',
				// Links to other resources like images
				'/"'.$path_escaped.'(.+?)"/m',
			],
			[
				'/documentation/?page=$1',
				$this->url.'$1',
			],
			$html
		);

		$json->parse->text->{'*'} = $html;

		return $json->parse;
	}

	private $url;
	private $wikipath;
}
