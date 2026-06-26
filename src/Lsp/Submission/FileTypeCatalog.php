<?php

declare(strict_types=1);

namespace App\Lsp\Submission;

use Doctrine\DBAL\Connection;

final class FileTypeCatalog
{
    public function __construct(private readonly Connection $connection) {}

    /**
     * @return list<string>
     */
    public function allowedExtensions(): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT DISTINCT extension FROM filetypes ORDER BY extension',
        );
    }

    /**
     * @return list<array{value: string}>
     */
    public function categoriesFor(string $extension): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT CONCAT(categories.name, \' - \', subcategories.name) AS value
            FROM filetypes
            INNER JOIN categories ON categories.id = filetypes.category
            INNER JOIN subcategories ON subcategories.category = categories.id
            WHERE LOWER(extension) = LOWER(:ext)
            ORDER BY categories.name, subcategories.name',
            ['ext' => $extension],
        );
    }

    /**
     * @return list<string>
     */
    public function licenses(): array
    {
        return $this->connection->fetchFirstColumn('SELECT name FROM licenses ORDER BY name');
    }

    public function licenseIdByName(string $name): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT id FROM licenses WHERE name = :name',
            ['name' => $name],
        );
    }

    public function permits(string $extension): bool
    {
        return $this->categoriesFor($extension) !== [];
    }

    public function extensionOf(string $filename): string
    {
        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $base = strtolower(pathinfo($filename, PATHINFO_FILENAME));
        if (str_ends_with($base, '.tar')) {
            return '.tar.' . $ext;
        }
        return '.' . $ext;
    }
}
