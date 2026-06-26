<?php
namespace App\News;

use LMMS\GraphQl;

final class GitHubNewsRepository implements NewsRepository
{
	private const ANNOUNCEMENTS_CATEGORY = 'DIC_kwDOAPDEUM4CowUM';

	public function __construct(private readonly GraphQl $graphql) {}

	public function findAll(): array
	{
		$results = $this->graphql->executeQuery($this->query());
		return array_map(
			fn(array $edge) => $this->toEntry($edge['node']),
			$results['data']['repository']['discussions']['edges'],
		);
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
			date: $date,
			title: $title,
			url: $node['url'],
			author: $node['author']['login'],
			bodyHtml: $node['bodyHTML'],
		);
	}

	private function query(int $limit = 100): string
	{
		$category = self::ANNOUNCEMENTS_CATEGORY;
		return <<<GRAPHQL
		{
			repository(owner: "%owner%", name: "%repo%") {
				discussions(categoryId: "$category", first: $limit, orderBy: {field: CREATED_AT, direction: DESC}) {
					edges {
						node {
							author { login },
							url,
							createdAt,
							bodyHTML
						}
					}
				}
			}
		}
		GRAPHQL;
	}
}
