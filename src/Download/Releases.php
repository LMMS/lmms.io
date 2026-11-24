<?php
namespace App\Download;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Releases
{
	public function __construct(
		private HttpClientInterface $githubClient,
		private CacheInterface $githubCache,
		private string $owner,
		private string $repo
	)
	{ }

	public function latestStableAssets(): array
	{
		return $this->latestAssets(true);
	}

	public function latestUnstableAssets(): array
	{
		return $this->latestAssets(false);
	}

	private function all(): array
	{
		return $this->githubCache->get('releases', function () {
			$json = $this->githubClient->request('GET', "/repos/$this->owner/$this->repo/releases")->toArray();
			usort($json, function ($a, $b) {
				return version_compare($b['tag_name'], $a['tag_name']);
			});
			return $json;
		});
	}

	private function latestAssets(bool $stable = true): array
	{
		foreach ($this->all() as $release) {
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
			$parser = new Platform($asset['name']);
			return new Asset(
				platform: $parser,
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
}
