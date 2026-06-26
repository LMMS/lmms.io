<?php

declare(strict_types=1);

namespace App\Lsp\Comment;

use Doctrine\DBAL\Connection;

final class CommentRepository
{
    public function __construct(private readonly Connection $connection) {}

    public function add(int $fileId, int $userId, string $text): void
    {
        $this->connection->executeStatement(
            'INSERT INTO comments (user_id, file_id, text) VALUES (:userId, :fileId, :text)',
            ['userId' => $userId, 'fileId' => $fileId, 'text' => $text],
        );
    }
}
