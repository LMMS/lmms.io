<?php

declare(strict_types=1);

namespace App\Lsp\Download;

use Doctrine\DBAL\Connection;

final class DownloadRepository
{
    public function __construct(private readonly Connection $connection) {}

    public function findFilenameById(int $id): ?string
    {
        $filename = $this->connection->fetchOne(
            'SELECT filename FROM files WHERE id = :id',
            ['id' => $id],
        );

        return $filename === false ? null : (string) $filename;
    }

    /**
     * @return array{id: int, filename: string}|null
     */
    public function findByHash(string $hash): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, filename FROM files WHERE hash = :hash',
            ['hash' => $hash],
        );

        if ($row === false) {
            return null;
        }

        return ['id' => (int) $row['id'], 'filename' => (string) $row['filename']];
    }

    public function incrementCounter(int $id): void
    {
        $this->connection->executeStatement(
            'UPDATE files SET downloads = downloads + 1 WHERE id = :id',
            ['id' => $id],
        );
    }
}
