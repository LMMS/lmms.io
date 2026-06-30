<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\User;
use App\Security\UserProvider;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class UserProviderTest extends KernelTestCase
{
    private Connection $connection;
    private UserProvider $provider;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->connection = self::getContainer()->get(Connection::class);
        $this->provider = self::getContainer()->get(UserProvider::class);

        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'testuser']);
        $this->connection->executeStatement(
            'INSERT INTO users (login, password, realname, is_admin, loginFailureCount) VALUES (:login, :sha1, :realname, 0, 0)',
            [
                'login' => 'testuser',
                'sha1' => sha1('secret'),
                'realname' => 'Test User',
            ],
        );
    }

    protected function tearDown(): void
    {
        $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => 'testuser']);
        parent::tearDown();
    }

    public function testLoadByIdentifierReturnsUser(): void
    {
        $user = $this->provider->loadUserByIdentifier('testuser');

        self::assertInstanceOf(User::class, $user);
        self::assertSame('testuser', $user->getUserIdentifier());
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testLoadByIdentifierIsCaseInsensitive(): void
    {
        $user = $this->provider->loadUserByIdentifier('TESTUSER');

        self::assertSame('testuser', $user->getUserIdentifier());
    }

    public function testLoadByIdentifierThrowsForUnknownUser(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('nobody');
    }

    public function testRefreshUser(): void
    {
        $stale = new User(999, 'testuser', 'oldhash', false);
        $fresh = $this->provider->refreshUser($stale);

        self::assertSame('testuser', $fresh->getUserIdentifier());
    }

    public function testSupportsUser(): void
    {
        self::assertTrue($this->provider->supportsClass(User::class));
        self::assertFalse($this->provider->supportsClass(\stdClass::class));
    }
}
