<?php
namespace App\News;

interface NewsRepository
{
	/** @return NewsEntry[] newest first */
	public function findAll(): array;

	public function findOneByDate(string $date): ?NewsEntry;
}
