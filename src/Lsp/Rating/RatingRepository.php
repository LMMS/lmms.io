<?php

declare(strict_types=1);

namespace App\Lsp\Rating;

use Doctrine\DBAL\Connection;

final class RatingRepository
{
    public function __construct(private readonly Connection $connection) {}

    public function set(int $fileId, int $userId, int $stars): void
    {
        $existing = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ratings WHERE file_id = :fileId AND user_id = :userId',
            ['fileId' => $fileId, 'userId' => $userId],
        );

        if ($existing > 0) {
            $this->connection->executeStatement(
                'UPDATE ratings SET stars = :stars WHERE file_id = :fileId AND user_id = :userId',
                ['stars' => $stars, 'fileId' => $fileId, 'userId' => $userId],
            );
        } else {
            $this->connection->executeStatement(
                'INSERT INTO ratings (file_id, user_id, stars) VALUES (:fileId, :userId, :stars)',
                ['fileId' => $fileId, 'userId' => $userId, 'stars' => $stars],
            );
        }
    }
}
