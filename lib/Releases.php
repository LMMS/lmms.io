<?php
namespace LMMS;

use Github\Client;

class Releases
{
	public function __construct(Client $client, string $owner, string $repo)
	{
		$this->json = $client->repo()->releases()->all($owner, $repo);
		usort($this->json, function ($a, $b) {
			return version_compare($b['tag_name'], $a['tag_name']);
		});
	}

	public function latestStableAssets(): array
	{
		return $this->latestAssets(true);
	}

	public function latestUnstableAssets(): array
	{
		return $this->latestAssets(false);
	}

	private function latestAssets(bool $stable = true): array
	{
		foreach ($this->json as $release) {
			if ($release['draft']) {
				continue;
			}
			if ($release['prerelease'] === $stable) {
				continue;
			}

			return $this->mapAssetsFromJson($release);
		}
		return [];
	}

	private function mapAssetsFromJson(array $json): array
	{
		return array_map(function (array $asset) use ($json) {
			return new Asset(
				platform: self::platformFromAssetName($asset['name']),
				platformName: self::platformNameFromAssetName($asset['name']),
				releaseName: $json['name'],
				downloadUrl: $asset['browser_download_url'],
				description: $json['body'],
				gitRef: $json['tag_name'],
				date: $asset['created_at']
			);
		}, $json['assets']);
	}

	/*
	 * Get "32-bit", "64-bit", etc based on Download URL
	 */
	private static function getArchitectureFromAssetName(string $assetName): string {
		$arch64 = array('amd64', 'win64', 'x86-64', 'x86_64', 'x64', '64-bit', '.dmg');
		foreach ($arch64 as $x) {
			if (strpos(strtolower($assetName), $x) !== false) {
				return '64-bit';
			}
		}
		return '32-bit';
	}

	/*
	 * Get "10.11", etc based on Download URL
	 */
	private static function getOsVersionFromAssetName(string $assetName): string {
		if (strpos($assetName, '.dmg') !== false) {
			$parts = explode('-', explode('.dmg', $assetName)[0]);
			return filter_var(array_pop($parts), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		}
		return 'all';
	}

	private static function platformFromAssetName(string $assetName): Platform {
		if (strpos($assetName, '.deb') !== false) {
			return Platform::Linux;
		} else if (strpos($assetName, '.rpm') !== false) {
			return Platform::Linux;
		} else if (strpos($assetName, '.dmg') !== false) {
			return Platform::MacOS;
		} else if (strpos($assetName, '.exe') !== false) {
			return Platform::Windows;
		} else if (strpos($assetName, '.AppImage') !== false) {
			return Platform::Linux;
		} else {
			return Platform::Unknown;
		}
	}

	/*
	 * Get "Windows", "Apple", etc based on Download URL
	 */
	private static function platformNameFromAssetName(string $assetName): string {
		if (strpos($assetName, '.tar.') !== false) {
			return 'Source Tarball';
		} else if (strpos($assetName, '.deb') !== false) {
			return 'Ubuntu ' . self::getArchitectureFromAssetName($assetName);
		} else if (strpos($assetName, '.rpm') !== false) {
			return 'Fedora ' . self::getArchitectureFromAssetName($assetName);
		} else if (strpos($assetName, '.dmg') !== false) {
			return 'macOS ' . self::getOsVersionFromAssetName($assetName) . '+';
		} else if (strpos($assetName, '.exe') !== false) {
			return 'Windows ' . self::getArchitectureFromAssetName($assetName);
		} else if (strpos($assetName, '.AppImage') !== false) {
			return 'Linux ' . self::getArchitectureFromAssetName($assetName);
		} else {
			return $assetName;
		}
	}

	private $json;
}
