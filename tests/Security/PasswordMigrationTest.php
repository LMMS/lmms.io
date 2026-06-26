<?php

declare(strict_types=1);

namespace App\Tests\Security;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PasswordMigrationTest extends WebTestCase
{
    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = $this->client->getContainer()->get(Connection::class);

        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'migrationtest']);
        $this->connection->executeStatement(
            'INSERT INTO users (login, password, realname, is_admin, loginFailureCount) VALUES (:login, :sha1, :realname, 0, 0)',
            ['login' => 'migrationtest', 'sha1' => sha1('oldpassword'), 'realname' => 'Migration Test'],
        );
    }

    protected function tearDown(): void
    {
        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'migrationtest']);
        parent::tearDown();
    }

    public function testLegacySha1HashIsRehashedAfterSuccessfulLogin(): void
    {
        $before = $this->connection->fetchOne('SELECT password FROM users WHERE login = :l', ['l' => 'migrationtest']);
        self::assertSame(sha1('oldpassword'), $before, 'precondition: password is stored as legacy SHA1');

        // Authenticate through the real firewall (test env uses http_basic).
        $this->client->request('GET', '/lsp/settings', [], [], [
            'PHP_AUTH_USER' => 'migrationtest',
            'PHP_AUTH_PW' => 'oldpassword',
        ]);
        self::assertResponseIsSuccessful();

        $after = $this->connection->fetchOne('SELECT password FROM users WHERE login = :l', ['l' => 'migrationtest']);
        self::assertNotSame($before, $after, 'password should be rehashed away from SHA1 after login');
        self::assertStringStartsWith('$', $after, 'rehashed password should use a modern crypt hash');
    }
}
