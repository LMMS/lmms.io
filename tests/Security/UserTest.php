<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testGetters(): void
    {
        $user = new User(42, 'alice', 'hashedpw', true);

        self::assertSame(42, $user->getId());
        self::assertSame('alice', $user->getUserIdentifier());
        self::assertSame('hashedpw', $user->getPassword());
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
    }

    public function testNonAdminHasOnlyUserRole(): void
    {
        $user = new User(1, 'bob', 'hashedpw', false);

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testEraseCredentials(): void
    {
        $user = new User(1, 'alice', 'hashedpw', false);
        $user->eraseCredentials();

        self::assertSame('hashedpw', $user->getPassword());
    }
}
