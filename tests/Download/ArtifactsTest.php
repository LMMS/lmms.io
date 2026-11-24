<?php

declare(strict_types=1);

namespace App\Tests\Download;

use App\Download\Artifacts;
use App\Tests\GitHub\FakeGitHub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArtifactsTest extends TestCase
{
    private const REPO = '/repos/LMMS/lmms';

    private MockClock $clock;
    private ArrayAdapter $cache;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->cache = new ArrayAdapter(defaultLifetime: 600, clock: $this->clock);
    }

    public function testBranchBuildsAreCachedWithoutExpiredArtifacts(): void
    {
        $github = new FakeGitHub([
            'GET ' . self::REPO . '/actions/workflows/build.yml/runs' => ['total_count' => 1, 'workflow_runs' => [['id' => 11]]],
            'GET ' . self::REPO . '/actions/runs/11/artifacts'        => self::runArtifacts(),
        ]);
        $artifacts = $this->artifacts($github);

        $assets = $artifacts->getForBranch('master');
        $artifacts->getForBranch('master');

        self::assertSame(['/download/artifact/1'], array_map(fn($asset) => $asset->getDownloadUrl(), $assets));
        self::assertCount(2, $github->requests);
    }

    public function testPullRequestBuildsAreCached(): void
    {
        $github = new FakeGitHub(self::pullRequestRoutes(checkRun: 'linux-x86_64'));
        $artifacts = $this->artifacts($github);

        $assets = $artifacts->getForPullRequest(42);
        $this->clock->sleep(61);
        $artifacts->getForPullRequest(42);

        self::assertSame("## Add a synth\nIt's loud", $assets[0]->getDescription());
        self::assertSame('#42 @abcdef1', $assets[0]->getReleaseName());
        self::assertCount(4, $github->requests);
    }

    public function testMissingBuildIsCheckedAgainAfterAMinute(): void
    {
        $github = new FakeGitHub(self::pullRequestRoutes(checkRun: 'lint'));
        $artifacts = $this->artifacts($github);

        self::assertSame([], $artifacts->getForPullRequest(42));
        $this->clock->sleep(30);
        $artifacts->getForPullRequest(42);
        self::assertCount(2, $github->requests);

        $github->routes = self::pullRequestRoutes(checkRun: 'linux-x86_64');
        $this->clock->sleep(31);
        self::assertCount(1, $artifacts->getForPullRequest(42));
    }

    public function testDownloadUrlIsNeverCached(): void
    {
        $github = new FakeGitHub([
            'GET ' . self::REPO . '/actions/artifacts/1/zip' => new MockResponse('', [
                'http_code'        => 302,
                'response_headers' => ['Location' => 'https://artifacts.example/signed.zip'],
            ]),
        ]);
        $artifacts = $this->artifacts($github);

        self::assertSame('https://artifacts.example/signed.zip', $artifacts->getArtifactDownloadUrl(1));
        $artifacts->getArtifactDownloadUrl(1);

        self::assertCount(2, $github->requests);
    }

    public function testMissingPullRequestKeepsGitHubsStatus(): void
    {
        $this->expectExceptionCode(404);

        $this->artifacts(new FakeGitHub([]))->getForPullRequest(404);
    }

    public function testMissingArtifactKeepsGitHubsStatus(): void
    {
        $this->expectExceptionCode(404);

        $this->artifacts(new FakeGitHub([]))->getArtifactDownloadUrl(404);
    }

    private function artifacts(FakeGitHub $github): Artifacts
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willReturnCallback(fn(string $route, array $params) => '/download/artifact/' . $params['id']);

        return new Artifacts($github->client(), $this->cache, 'LMMS', 'lmms', 'build.yml', $router);
    }

    private static function pullRequestRoutes(string $checkRun): array
    {
        return [
            'GET ' . self::REPO . '/pulls/42'                      => ['title' => 'Add a synth', 'body' => "It's loud", 'head' => ['sha' => 'abcdef1234']],
            'GET ' . self::REPO . '/commits/abcdef1234/check-runs' => ['check_runs' => [['id' => 7, 'name' => $checkRun, 'app' => ['slug' => 'github-actions']]]],
            'GET ' . self::REPO . '/actions/jobs/7'                => ['run_id' => 11],
            'GET ' . self::REPO . '/actions/runs/11/artifacts'     => self::runArtifacts(),
        ];
    }

    private static function runArtifacts(): array
    {
        $artifact = fn(int $id, bool $expired) => [
            'id'           => $id,
            'name'         => 'linux-x86_64',
            'expired'      => $expired,
            'created_at'   => '2026-11-20T12:00:00Z',
            'workflow_run' => ['head_sha' => 'abcdef1234'],
        ];

        return ['artifacts' => [$artifact(1, false), $artifact(2, true)]];
    }
}
