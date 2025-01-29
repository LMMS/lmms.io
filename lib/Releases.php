<?php
namespace LMMS;

use Github\Client;
use LMMS\PlatformParser;

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
		$assets = array_map(function (array $asset) use ($json) {
			$parser = new PlatformParser($asset['name']);
			return new Asset(
				platform: $parser->getPlatform(),
				platformName: $parser, // __toString()
				releaseName: $json['name'],
				downloadUrl: $asset['browser_download_url'],
				description: $json['body'],
				gitRef: $json['tag_name'],
				date: $asset['created_at']
			);
		}, $json['assets']);

		// Cheap sort to make 64-bit buttons appear first
		usort($assets, function ($a, $b) {
			return strcmp($a->getPlatformName(), $b->getPlatformName());
		});
		return $assets;
	}

	private $json;
}
