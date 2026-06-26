<?php
namespace App\News;

interface NewsRepository
{
	/** @return NewsEntry[] newest first */
	public function findAll(): array;

	public function findLatest(): ?NewsEntry;

	public function findOneByDate(string $date): ?NewsEntry;

	/** @return array{prev: ?NewsEntry, next: ?NewsEntry} */
	public function findNeighbors(string $date): array;
}
