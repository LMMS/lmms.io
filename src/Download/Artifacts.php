<?php
namespace App\Download;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Artifacts
{
	public function __construct(
		private HttpClientInterface $githubClient,
		private CacheInterface $githubCache,
		private string $owner,
		private string $repo,
		private string $workflow,
		private UrlGeneratorInterface $router
	)
	{ }

	public function getForBranch(string $branch): array
	{
		$artifacts = $this->githubCache->get('artifacts.branch.' . rawurlencode($branch), function (ItemInterface $item) use ($branch) {
			// Get the latest successful runs of the target workflow
			$runs = $this->get("actions/workflows/$this->workflow/runs", [
				'event' => 'push',
				'branch' => $branch,
				'status' => 'success',
				'per_page' => 1
			]);
			if ($runs['total_count'] == 0) {
				return $this->keepBriefly($item, []);
			}
			// Get all artifacts of that run
			$runId = $runs['workflow_runs'][0]['id'];
			return $this->keepBriefly($item, $this->validArtifacts($runId));
		});
		return $this->mapBranchAssetsFromJson($artifacts);
	}

	public function getForPullRequest(int $id): array
	{
		['artifacts' => $artifacts, 'description' => $description] = $this->githubCache->get('artifacts.pr.' . $id, function (ItemInterface $item) use ($id) {
			// Get the most recent commit in the pull request
			$pr = $this->get("pulls/$id");
			$ref = $pr['head']['sha'];
			$description = '## ' . $pr['title'] . "\n" . $pr['body'];

			// Get all check runs for that commit
			$checks = $this->get("commits/$ref/check-runs");

			// Find a check run corresponding to the GitHub Actions build workflow
			foreach ($checks['check_runs'] as $run) {
				if ($run['app']['slug'] === 'github-actions') {
					$parser = new Platform($run['name']);
					if($parser->found()) {
						$jobId = $run['id'];
						break;
					}
				}
			}
			if (!isset($jobId)) {
				return ['artifacts' => $this->keepBriefly($item, []), 'description' => $description];
			}

			// Get the GitHub Actions workflow run corresponding to that check run
			$job = $this->get("actions/jobs/$jobId");
			$runId = $job['run_id'];

			return ['artifacts' => $this->keepBriefly($item, $this->validArtifacts($runId)), 'description' => $description];
		});
		return $this->mapPullRequestAssetsFromJson($artifacts, $id, $description);
	}

	public function getArtifactDownloadUrl(int $artifactId): string
	{
		$response = $this->githubClient->request('GET', "/repos/$this->owner/$this->repo/actions/artifacts/$artifactId/zip", [
			'max_redirects' => 0,
		]);
		if ($response->getStatusCode() !== 302) {
			$response->getHeaders(); // throws, with the status as the code
		}
		return $response->getHeaders(false)['location'][0];
	}

	private function get(string $path, array $query = []): array
	{
		return $this->githubClient->request('GET', "/repos/$this->owner/$this->repo/$path", ['query' => $query])->toArray();
	}

	private function validArtifacts(int $runId): array
	{
		$artifacts = $this->get("actions/runs/$runId/artifacts");

		return array_values(array_filter($artifacts['artifacts'], function ($artifact) {
			return !$artifact['expired'];
		}));
	}

	// A build that's still running has no artifacts yet, so check again soon
	private function keepBriefly(ItemInterface $item, array $artifacts): array
	{
		if (!$artifacts) {
			$item->expiresAfter(60);
		}
		return $artifacts;
	}

	private function mapBranchAssetsFromJson(array $json): array
	{
		return array_map(function (array $artifact) {
			$parsed = new Platform($artifact['name']);
			return new Asset(
				platform: $parsed,
				platformName: $parsed, // __toString()
				releaseName: '@' . substr($artifact['workflow_run']['head_sha'], 0, 7),
				downloadUrl: $this->router->generate('download_artifact', ['id' => $artifact['id']]),
				description: null,
				gitRef: $artifact['workflow_run']['head_sha'],
				date: $artifact['created_at']
			);
		}, $json);
	}

	private function mapPullRequestAssetsFromJson(array $json, string $pr, string $description): array
	{
		return array_map(function (array $artifact) use ($pr, $description) {
			$parsed = new Platform($artifact['name']);
			return new Asset(
				platform: $parsed,
				platformName: $parsed, // __toString()
				releaseName: '#' . $pr . ' @' . substr($artifact['workflow_run']['head_sha'], 0, 7),
				downloadUrl: $this->router->generate('download_artifact', ['id' => $artifact['id']]),
				description: $description,
				gitRef: $artifact['workflow_run']['head_sha'],
				date: $artifact['created_at']
			);
		}, $json);
	}
}
