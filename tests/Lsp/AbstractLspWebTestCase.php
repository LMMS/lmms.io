<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractLspWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = $this->client->getContainer()->get(Connection::class);

        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'lsptest']);
        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'otheruser']);
        $this->connection->executeStatement(
            'INSERT INTO users (login, password, realname, is_admin, loginFailureCount) VALUES (:login, :sha1, :realname, 0, 0)',
            ['login' => 'lsptest', 'sha1' => sha1('lsptestpass'), 'realname' => 'LSP Test'],
        );
        $this->connection->executeStatement(
            'INSERT INTO users (login, password, realname, is_admin, loginFailureCount) VALUES (:login, :sha1, :realname, 0, 0)',
            ['login' => 'otheruser', 'sha1' => sha1('otheruserpass'), 'realname' => 'Other User'],
        );
    }

    protected function tearDown(): void
    {
        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'lsptest']);
        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'otheruser']);
        parent::tearDown();
    }

    protected function setSessionCaptcha(string $code): void
    {
        $this->client->request('GET', '/lsp/register');
        $session = $this->client->getRequest()->getSession();
        $session->set('captcha', $code);
        $session->save();
    }

    protected function createTestFile(string $ownerLogin = 'lsptest'): int
    {
        $userId = (int) $this->connection->fetchOne(
            'SELECT id FROM users WHERE login = :login',
            ['login' => $ownerLogin],
        );
        $categoryId = (int) $this->connection->fetchOne('SELECT id FROM categories LIMIT 1');
        $subcategoryId = (int) $this->connection->fetchOne(
            'SELECT id FROM subcategories WHERE category = :cat LIMIT 1',
            ['cat' => $categoryId],
        );
        $licenseId = (int) $this->connection->fetchOne('SELECT id FROM licenses LIMIT 1');

        $this->connection->executeStatement(
            'INSERT INTO files (filename, user_id, insert_date, update_date, category, subcategory, license_id, description, size, hash)
             VALUES (:fn, :uid, NOW(), NOW(), :cat, :sub, :lic, :desc, 0, :hash)',
            [
                'fn'   => 'lsptest-fixture-' . uniqid() . '.mmpz',
                'uid'  => $userId,
                'cat'  => $categoryId,
                'sub'  => $subcategoryId,
                'lic'  => $licenseId,
                'desc' => '',
                'hash' => 'lsptestfixturehash-' . uniqid(),
            ],
        );

        return (int) $this->connection->lastInsertId();
    }

    protected function deleteTestFile(int $fileId): void
    {
        $this->connection->executeStatement('DELETE FROM comments WHERE file_id = :id', ['id' => $fileId]);
        $this->connection->executeStatement('DELETE FROM ratings WHERE file_id = :id', ['id' => $fileId]);
        $this->connection->executeStatement('DELETE FROM files WHERE id = :id', ['id' => $fileId]);
    }

    /**
     * @return array{0: string, 1: int, 2: int} {"Category - Subcategory", categoryId, subcategoryId}
     */
    protected function pickCategoryPair(): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT categories.id AS cid, categories.name AS cname,
                    subcategories.id AS sid, subcategories.name AS sname
             FROM subcategories
             INNER JOIN categories ON categories.id = subcategories.category
             LIMIT 1',
        );

        return [$row['cname'] . ' - ' . $row['sname'], (int) $row['cid'], (int) $row['sid']];
    }

    protected function pickLicenseName(): string
    {
        return (string) $this->connection->fetchOne('SELECT name FROM licenses LIMIT 1');
    }
}
