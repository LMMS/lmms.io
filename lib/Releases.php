<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/GitHubClient.php');

class Releases
{
	public function __construct($owner='LMMS', $repo='lmms')
	{
		$client = new \LMMS\GitHubClient(
			new \LMMS\SafeCachedHttpClient(['cache_dir' => '/tmp/github-api-cache'])
		);
		$this->json = $client->api('repo')->releases()->all($owner, $repo);
	}

	public function latestAsset($pattern, $stable = true)
	{
		foreach ($this->json as $index => $release) {
			if ($release['prerelease'] === $stable) {
				continue;
			}

			foreach($release['assets'] as $asset)
			{
				if (preg_match($pattern, $asset['name']))
				{
					$asset['release'] = $release;
					$asset['osname'] = $this->osName($asset['name']);
					$asset['release']['index'] = $index;
					return $asset;
				}
			}
		}
	}

	public function latestWin32Asset($stable = true)
	{
		return $this->latestAsset('/.*-win32\.exe/i', $stable);
	}

	public function latestWin64Asset($stable = true)
	{
		return $this->latestAsset('/.*-win64\.exe/i', $stable);
	}

	public function latestOSXAsset($stable = true)
	{
		return $this->latestAsset('/.*\.dmg/', $stable);
	}

	/*
	 * Get "32-bit", "64-bit", etc based on Download URL
	 */
	private function get_arch($text) {
		$arch64 = array('amd64', 'win64', 'x86-64', 'x64', '64-bit', '.dmg');
		foreach ($arch64 as $x) {
			if (strpos(strtolower($text), $x) !== false) {
				return '64-bit';
			}
		}
		return '32-bit';
	}

	/*
	 * Get "Windows", "Apple", etc based on Download URL
	 */
	private function osName($text) {
		if (strpos($text, '.tar.') !== false) {
			return 'Source Tarball';
		} else if (strpos($text, '.deb') !== false) {
			return 'Ubuntu ' . $this->get_arch($text);
		} else if (strpos($text, '.rpm') !== false) {
			return 'Fedora ' . $this->get_arch($text);
		} else if (strpos($text, '.dmg') !== false) {
			return 'Apple OS X';
		} else if (strpos($text, '.exe') !== false) {
			return 'Windows ' . $this->get_arch($text);
		} else {
			return $text;
		}
	}

	private $json;
}
