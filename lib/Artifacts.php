<?php
namespace LMMS;

use Github\Client;
use LMMS\HttpClientPlugin\UriRecordPlugin;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Artifacts
{
	public function __construct(
		private Client $client,
		private string $owner,
		private string $repo,
		private string $workflow,
		private UrlGeneratorInterface $router
	)
	{ }

	public function getForBranch(string $branch): array
	{
		// Get the latest successful runs of the target workflow
		$runs = $this->client->repo()->workflowRuns()->listRuns($this->owner, $this->repo, $this->workflow, [
			'event' => 'push',
			'branch' => $branch,
			'status' => 'success',
			'per_page' => 1
		]);
		if ($runs['total_count'] > 0) {
			// Get all artifacts of that run
			$runId = $runs['workflow_runs'][0]['id'];
			$artifacts = $this->client->repo()->artifacts()->runArtifacts($this->owner, $this->repo, $runId);

			$validArtifacts = array_filter($artifacts['artifacts'], function ($artifact) {
				return !$artifact['expired'];
			});
			return $this->mapBranchAssetsFromJson($validArtifacts);
		}
		return [];
	}

	public function getForPullRequest(int $id): array
	{
		// Get the most recent commit in the pull request
		$pr = $this->client->pr()->show($this->owner, $this->repo, $id);
		$ref = $pr['head']['sha'];

		// Get all check runs for that commit
		$checks = $this->client->repo()->checkRuns()->allForReference($this->owner, $this->repo, $ref);

		// Find a check run corresponding to the GitHub Actions build workflow
		foreach ($checks['check_runs'] as $run) {
			if ($run['app']['slug'] === 'github-actions') {
				switch($run['name']) {
					case 'linux': // discontinued
					case 'linux-x86_64':
					case 'linux-arm64':
					case 'macos': // discontinued
					case 'macos-arm64':
					case 'macos-x86_64':
					case 'msvc-x86': // discontinued
					case 'msvc-x64':
					case 'mingw32': // discontinued
					case 'mingw64':
					case 'msys2-arm64':
						$jobId = $run['id'];
						break 2;  /* Exit the switch and the foreach */
				}
			}
		}
		if (!isset($jobId)) { return []; }

		// Get the GitHub Actions workflow run corresponding to that check run
		$job = $this->client->repo()->workflowJobs()->show($this->owner, $this->repo, $jobId);
		$runId = $job['run_id'];

		// Get all artifacts of that workflow run
		$artifacts = $this->client->repo()->artifacts()->runArtifacts($this->owner, $this->repo, $runId);

		$validArtifacts = array_filter($artifacts['artifacts'], function ($artifact) {
			return !$artifact['expired'];
		});
		$description = '## ' . $pr['title'] . "\n" . $pr['body'];
		return $this->mapPullRequestAssetsFromJson($validArtifacts, $id, $description);
	}

	public function getArtifactDownloadUrl(int $artifactId): string
	{
		$this->client->repo()->artifacts()->download($this->owner, $this->repo, $artifactId);
		return $this->client->getLastResponse()->getHeader(UriRecordPlugin::HEADER_NAME)[0];
	}

	private function mapBranchAssetsFromJson(array $json): array
	{
		return array_map(function (array $artifact) {
			return new Asset(
				platform: self::platformFromArtifactName($artifact['name']),
				platformName: self::platformNameFromArtifactName($artifact['name']),
				releaseName: 'g' . substr($artifact['workflow_run']['head_sha'], 0, 9),
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
			return new Asset(
				platform: self::platformFromArtifactName($artifact['name']),
				platformName: self::platformNameFromArtifactName($artifact['name']),
				releaseName: '#' . $pr . '@' . substr($artifact['workflow_run']['head_sha'], 0, 9),
				downloadUrl: $this->router->generate('download_artifact', ['id' => $artifact['id']]),
				description: $description,
				gitRef: $artifact['workflow_run']['head_sha'],
				date: $artifact['created_at']
			);
		}, $json);
	}

	private static function platformFromArtifactName(string $artifactName): Platform
	{
		switch ($artifactName) {
			case 'linux': // discontinued
			case 'linux-x86_64':
			case 'linux-arm64':
				return Platform::Linux;
			case 'macos': // discontinued
			case 'macos-arm64':
			case 'macos-x86_64':
				return Platform::MacOS;
			case 'msvc-x86': // discontinued
			case 'msvc-x64':
			case 'mingw32': // discontinued
			case 'mingw64':
			case 'msys2-arm64':
				 return Platform::Windows;
			default:
				return Platform::Unknown;
		}
	}

	private static function platformNameFromArtifactName(string $artifactName): string
	{
		switch($artifactName) {
			case 'linux': // discontinued
			case 'linux-x86_64':
				return 'Linux';
			case 'linux-arm64':
				return 'Linux ARM64';
			case 'macos': // discontinued
			case 'macos-x86_64':
				return 'macOS Intel';
			case 'macos-arm64':
				return 'macOS';
			case 'mingw32': // discontinued
				return 'Windows 32-bit (mingw)';
			case 'mingw64':
				return 'Windows (mingw)';
			case 'msvc-x86': // discontinued
				return 'Windows 32-bit (msvc)';
			case 'msvc-x64':
				return 'Windows (msvc)';
			case 'msys2-arm64':
				return 'Windows ARM64 (mingw)';
			default:
				return 'Unknown (' . $artifactName . ')';
		}
	}
}
