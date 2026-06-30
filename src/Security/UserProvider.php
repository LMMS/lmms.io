<?php

declare(strict_types=1);

namespace App\Security;

use App\Lsp\UserRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
final class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UserRepository $users,
    ) {}

    public function loadUserByIdentifier(string $identifier): User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, login, password, is_admin FROM users WHERE LOWER(login) = LOWER(:login)',
            ['login' => $identifier],
        );

        if ($row === false) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return new User(
            (int) $row['id'],
            $row['login'],
            $row['password'],
            (bool) $row['is_admin'],
        );
    }

    public function refreshUser(UserInterface $user): User
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    /**
     * Persists a rehashed password when the stored hash is outdated (e.g. a
     * legacy SHA1 hash upgraded to bcrypt after a successful login).
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $this->users->updatePassword($user->getUserIdentifier(), $newHashedPassword);
    }
}
