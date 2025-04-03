<?php
namespace LMMS;

use Github\Client;
use LMMS\HttpClientPlugin\UriRecordPlugin;
use LMMS\PlatformParser;
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
				$parser = new PlatformParser($run['name']);
				if($parser->found()) {
					$jobId = $run['id'];
					break;
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

    public function getLatestMonthlyReport(): string
    {
        try {
            $announcementsCategory = "DIC_kwDOAPDEUM4CowUM";

            // TODO: Guard this call
            $query = <<<GRAPHQL
			{
				repository(owner: "$this->owner", name: "$this->repo") {
					discussions(categoryId: "$announcementsCategory", first: 1, orderBy: {field: CREATED_AT, direction: DESC}) {
						edges {
							node {
								bodyHTML
							}
						}
					}
				}
			}
			GRAPHQL;

            $results = $this->client->api('graphql')->execute($query);

            // Process and filter discussions by tag
            foreach ($results['data']['repository']['discussions']['edges'] as $result) {
                // TODO: List all news updates
                return $result['node']['bodyHTML'];
            }
        } catch (\Throwable $th) {
            return "Sorry, there was an error retrieving the monthly report. They are available on <a href='https://github.com/$this->owner/$this->repo/discussions/categories/announcements'>GitHub discussions</a>" . "\n <pre>" . $th->getMessage() . "</pre>";
        }

        return "Monthly report not found. They are available on <a href='https://github.com/$this->owner/$this->repo/discussions/categories/announcements>GitHub discussions'</a>";
    }

	public function getArtifactDownloadUrl(int $artifactId): string
	{
		$this->client->repo()->artifacts()->download($this->owner, $this->repo, $artifactId);
		return $this->client->getLastResponse()->getHeader(UriRecordPlugin::HEADER_NAME)[0];
	}

	private function mapBranchAssetsFromJson(array $json): array
	{
		return array_map(function (array $artifact) {
			$parsed = new PlatformParser($artifact['name']);
			return new Asset(
				platform: $parsed->getPlatform(),
				platformName: $parsed, // __toString()
				releaseName: '@' . substr($artifact['workflow_run']['head_sha'], 0, 7),
				downloadUrl: $this->router->generate('download_artifact', ['id' => $artifact['id']]),
				description: $this->getLatestMonthlyReport(),
				gitRef: $artifact['workflow_run']['head_sha'],
				date: $artifact['created_at']
			);
		}, $json);
	}

	private function mapPullRequestAssetsFromJson(array $json, string $pr, string $description): array
	{
		return array_map(function (array $artifact) use ($pr, $description) {
			$parsed = new PlatformParser($artifact['name']);
			return new Asset(
				platform: $parsed->getPlatform(),
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
