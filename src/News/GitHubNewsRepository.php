<?php

namespace App\News;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GitHubNewsRepository implements NewsRepository
{
    private const ANNOUNCEMENTS_CATEGORY = 'DIC_kwDOAPDEUM4CowUM';

    private const QUERY = <<<'GRAPHQL'
        query ($owner: String!, $repo: String!, $category: ID!) {
            repository(owner: $owner, name: $repo) {
                discussions(categoryId: $category, first: 100, orderBy: {field: CREATED_AT, direction: DESC}) {
                    edges {
                        node {
                            author { login }
                            url
                            createdAt
                            bodyHTML
                        }
                    }
                }
            }
        }
        GRAPHQL;

    public function __construct(
        private readonly HttpClientInterface $githubClient,
        private readonly CacheInterface      $githubCache,
        private readonly string              $owner,
        private readonly string              $repo,
    ) {
    }

    public function findAll(): array
    {
        $edges = $this->githubCache->get('news', function () {
            $result = $this->githubClient->request('POST', '/graphql', ['json' => [
                'query'     => self::QUERY,
                'variables' => ['owner' => $this->owner, 'repo' => $this->repo, 'category' => self::ANNOUNCEMENTS_CATEGORY],
            ]])->toArray();
            if (isset($result['errors'])) {
                throw new \RuntimeException($result['errors'][0]['message']);
            }
            return $result['data']['repository']['discussions']['edges'];
        });
        return array_map(fn(array $edge) => $this->toEntry($edge['node']), $edges);
    }

    public function findLatest(): ?NewsEntry
    {
        return $this->findAll()[0] ?? null;
    }

    public function findOneByDate(string $date): ?NewsEntry
    {
        foreach ($this->findAll() as $entry) {
            if ($entry->slug() === $date) {
                return $entry;
            }
        }
        return null;
    }

    public function findNeighbors(string $date): array
    {
        $entries = $this->findAll();
        foreach ($entries as $i => $entry) {
            if ($entry->slug() === $date) {
                return [
                    'prev' => $entries[$i - 1] ?? null,
                    'next' => $entries[$i + 1] ?? null,
                ];
            }
        }
        return ['prev' => null, 'next' => null];
    }

    private function toEntry(array $node): NewsEntry
    {
        $date = new \DateTimeImmutable($node['createdAt']);
        $matched = preg_match('/<h1[^>]*>(.*?)<\/h1>/', $node['bodyHTML'], $m);
        $title = $matched ? trim(html_entity_decode(strip_tags($m[1]))) : $date->format('F j, Y, g:i a');

        return new NewsEntry(
            date    : $date,
            title   : $title,
            url     : $node['url'],
            author  : $node['author']['login'],
            bodyHtml: $node['bodyHTML'],
        );
    }
}
