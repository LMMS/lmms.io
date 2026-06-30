<?php

declare(strict_types=1);

namespace App\Lsp\Project;

use Doctrine\DBAL\Connection;

final class ProjectDeletion
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $lspDataDir,
    ) {}

    /**
     * Cascade-deletes a project: dependent comments and ratings first, then
     * the file row, then the on-disk blob. Done in one place so a caller
     * can't drop the row but leave the blob (or vice versa).
     */
    public function delete(int $id): void
    {
        $this->connection->executeStatement('DELETE FROM comments WHERE file_id = :id', ['id' => $id]);
        $this->connection->executeStatement('DELETE FROM ratings  WHERE file_id = :id', ['id' => $id]);
        $this->connection->executeStatement('DELETE FROM files    WHERE id      = :id', ['id' => $id]);

        $path = $this->lspDataDir . '/' . $id;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
