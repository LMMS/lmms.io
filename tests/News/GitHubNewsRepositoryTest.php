<?php

declare(strict_types=1);

namespace App\Tests\News;

use App\News\GitHubNewsRepository;
use App\Tests\GitHub\FakeGitHub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

final class GitHubNewsRepositoryTest extends TestCase
{
    public function testQueriesGitHubOnceForAllLookups(): void
    {
        $github = new FakeGitHub(['POST /graphql' => self::announcements()]);
        $news = $this->repository($github, new ArrayAdapter());

        self::assertSame('2026-09-06', $news->findLatest()?->slug());
        self::assertSame('LMMS 1.3.0-alpha.2 is out!', $news->findOneByDate('2026-09-06')?->title);
        ['prev' => $prev, 'next' => $next] = $news->findNeighbors('2026-09-06');
        self::assertNull($prev);
        self::assertSame('2025-08-01', $next?->slug());

        self::assertSame(['POST /graphql'], $github->requests);
    }

    public function testFailedQueryIsRetriedOnTheNextLookup(): void
    {
        $github = new FakeGitHub(['POST /graphql' => new JsonMockResponse(['message' => 'Bad Gateway'], ['http_code' => 502])]);
        $news = $this->repository($github, new ArrayAdapter());

        try {
            $news->findLatest();
            self::fail('Expected the GitHub error to surface');
        } catch (ServerExceptionInterface) {
        }

        $github->routes['POST /graphql'] = self::announcements();
        self::assertSame('2026-09-06', $news->findLatest()?->slug());
        self::assertCount(2, $github->requests);
    }

    public function testGraphQlErrorsAreNotCached(): void
    {
        $github = new FakeGitHub(['POST /graphql' => ['errors' => [['message' => 'Could not resolve to a Repository']]]]);
        $news = $this->repository($github, new ArrayAdapter());

        try {
            $news->findLatest();
            self::fail('Expected the GraphQL error to surface');
        } catch (\RuntimeException $e) {
            self::assertSame('Could not resolve to a Repository', $e->getMessage());
        }

        $github->routes['POST /graphql'] = self::announcements();
        self::assertSame('2026-09-06', $news->findLatest()?->slug());
    }

    public function testSendsOwnerAndRepoAsVariables(): void
    {
        $variables = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$variables) {
            $variables = json_decode($options['body'], true)['variables'];
            return new JsonMockResponse(self::announcements());
        }, 'https://api.github.com');

        (new GitHubNewsRepository($client, new ArrayAdapter(), 'LMMS', 'lmms'))->findAll();

        self::assertSame(['owner' => 'LMMS', 'repo' => 'lmms', 'category' => 'DIC_kwDOAPDEUM4CowUM'], $variables);
    }

    private function repository(FakeGitHub $github, ArrayAdapter $cache): GitHubNewsRepository
    {
        return new GitHubNewsRepository($github->client(), $cache, 'LMMS', 'lmms');
    }

    private static function announcements(): array
    {
        $node = fn(string $createdAt, string $title) => ['node' => [
            'author'    => ['login' => 'lmms-bot'],
            'url'       => 'https://github.com/LMMS/lmms/discussions/1',
            'createdAt' => $createdAt,
            'bodyHTML'  => "<h1>$title</h1><p>Body</p>",
        ]];

        return ['data' => ['repository' => ['discussions' => ['edges' => [
            $node('2026-09-06T12:00:00Z', 'LMMS 1.3.0-alpha.2 is out!'),
            $node('2025-08-01T12:00:00Z', 'Best of LMMS XI'),
        ]]]]];
    }
}
