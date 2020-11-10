<?php
namespace LMMS;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Releases
{
	public function __construct($owner='LMMS', $repo='lmms')
	{
		$this->cache = new FilesystemAdapter();
		$this->client = new \Github\Client();
		$this->client->addCache($this->cache, [
			'default_ttl' => 1800
		]);
		$this->json = $this->client->api('repo')->releases()->all($owner, $repo);
		usort($this->json, function ($a, $b) {
			return version_compare($b['tag_name'], $a['tag_name']);
		});
	}

	public function latestAssets($pattern, $stable = true)
	{
		foreach ($this->json as $index => $release) {
			if ($release['draft']) {
				continue;
			}
			if ($release['prerelease'] === $stable) {
				continue;
			}

			$assets = [];
			foreach($release['assets'] as $asset)
			{
				if (preg_match($pattern, $asset['name']))
				{
					$asset['release'] = $release;
					$asset['osname'] = $this->osName($asset['name']);
					$asset['release']['index'] = $index;
					array_push($assets, $asset);
				}
			}
			return $assets;
		}
	}

	public function latestAsset($pattern, $stable = true)
	{
		$assets = $this->latestAssets($pattern, $stable);
		return $assets ? array_pop($assets) : null;
	}

	public function latestWin32Asset($stable = true)
	{
		return $this->latestAsset('/.*-win32\.exe/i', $stable);
	}

	public function latestWin64Asset($stable = true)
	{
		return $this->latestAsset('/.*-win64\.exe/i', $stable);
	}

	public function latestOSXAssets($stable = true)
	{
		return $this->latestAssets('/.*\.dmg/', $stable);
	}

	public function latestLinuxAssets($stable = true)
	{
		return $this->latestAssets('/.*\.AppImage/', $stable);
	}

	/*
	 * Get "32-bit", "64-bit", etc based on Download URL
	 */
	private function get_arch($text) {
		$arch64 = array('amd64', 'win64', 'x86-64', 'x86_64', 'x64', '64-bit', '.dmg');
		foreach ($arch64 as $x) {
			if (strpos(strtolower($text), $x) !== false) {
				return '64-bit';
			}
		}
		return '32-bit';
	}

	/*
	 * Get "10.11", etc based on Download URL
	 */
	private function get_osver($text) {
		if (strpos($text, '.dmg') !== false) {
			$parts = explode('-', explode('.dmg', $text)[0]);
			return filter_var(array_pop($parts), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		}
		return 'all';
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
			return 'macOS ' . $this->get_osver($text) . '+';
		} else if (strpos($text, '.exe') !== false) {
			return 'Windows ' . $this->get_arch($text);
		} else if (strpos($text, '.AppImage') !== false) {
			return 'Linux ' . $this->get_arch($text);
		} else {
			return $text;
		}
	}

	private $json;
	private $client;
}
