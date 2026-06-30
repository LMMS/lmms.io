<?php

declare(strict_types=1);

namespace App\Lsp;

use Doctrine\DBAL\Connection;

final class UserRepository
{
    public function __construct(private readonly Connection $connection) {}

    public function loginExists(string $login): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM users WHERE LOWER(login) = LOWER(:login)',
            ['login' => $login],
        );
    }

    public function getRealname(string $login): string
    {
        return (string) $this->connection->fetchOne(
            'SELECT realname FROM users WHERE LOWER(login) = LOWER(:login)',
            ['login' => $login],
        );
    }

    public function insert(string $login, string $realname, string $passwordHash): void
    {
        $this->connection->executeStatement(
            'INSERT INTO users (login, realname, password, is_admin, loginFailureCount)
            VALUES (:login, :realname, :password, 0, 0)',
            ['login' => $login, 'realname' => $realname, 'password' => $passwordHash],
        );
    }

    public function updateProfile(string $login, string $realname): void
    {
        $this->connection->executeStatement(
            'UPDATE users SET realname = :realname WHERE LOWER(login) = LOWER(:login)',
            ['realname' => $realname, 'login' => $login],
        );
    }

    public function updatePassword(string $login, string $passwordHash): void
    {
        $this->connection->executeStatement(
            'UPDATE users SET password = :password WHERE LOWER(login) = LOWER(:login)',
            ['password' => $passwordHash, 'login' => $login],
        );
    }
}
