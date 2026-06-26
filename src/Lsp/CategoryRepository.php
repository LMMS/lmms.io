<?php

declare(strict_types=1);

namespace App\Lsp;

use Doctrine\DBAL\Connection;

final class CategoryRepository
{
    public function __construct(private readonly Connection $connection) {}

    public function getAll(string $activeCategory = ''): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT categories.name AS name, COUNT(files.id) AS file_count, categories.id AS id
            FROM categories
            LEFT JOIN files ON files.category = categories.id
            GROUP BY categories.name, categories.id
            ORDER BY categories.id',
        );

        $result = [];
        foreach ($rows as $row) {
            $name = $row['name'] . ' (' . $row['file_count'] . ')';
            $url  = '/lsp/?action=browse&category=' . rawurlencode($row['name']);

            if ($activeCategory !== '' && $activeCategory === $row['name']) {
                $result[] = [$name, $url, $this->getSubcategories($row['name'], (int) $row['id'])];
            } else {
                $result[] = [$name, $url];
            }
        }

        return $result;
    }

    /**
     * Turns the form's "Category - Subcategory" select value into the matching
     * id pair. Returns 0 for either id when no row matches, mirroring the prior
     * inline lookups (an invalid pick produces an orphan row, not an error).
     *
     * @return array{0: int, 1: int}
     */
    public function resolveCombined(string $categorySubcategory): array
    {
        [$categoryName, $subcategoryName] = array_map('trim', explode(' - ', $categorySubcategory, 2));

        $categoryId = (int) $this->connection->fetchOne(
            'SELECT id FROM categories WHERE name = :name',
            ['name' => $categoryName],
        );
        $subcategoryId = (int) $this->connection->fetchOne(
            'SELECT id FROM subcategories WHERE category = :cat AND LOWER(name) = LOWER(:name)',
            ['cat' => $categoryId, 'name' => $subcategoryName],
        );

        return [$categoryId, $subcategoryId];
    }

    private function getSubcategories(string $category, int $categoryId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT subcategories.name AS name, COUNT(files.id) AS file_count
            FROM subcategories
            LEFT JOIN files ON files.subcategory = subcategories.id AND files.category = :id
            WHERE subcategories.category = :id
            GROUP BY subcategories.name
            ORDER BY subcategories.name',
            ['id' => $categoryId],
        );

        $result = [];
        foreach ($rows as $row) {
            $name          = $row['name'] . ' (' . $row['file_count'] . ')';
            $result[$name] = '/lsp/?action=browse&category=' . rawurlencode($category)
                           . '&subcategory=' . rawurlencode($row['name']);
        }

        return $result;
    }
}
