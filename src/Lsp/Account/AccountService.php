<?php

declare(strict_types=1);

namespace App\Lsp\Account;

use App\Lsp\UserRepository;
use App\Security\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class AccountService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {}

    /**
     * Returns null on success, or a user-facing error message describing the
     * first rule violated. On success the new account is persisted.
     */
    public function register(RegistrationAttempt $attempt, string $expectedCaptcha): ?string
    {
        $error = match (true) {
            $attempt->captcha === '' || $attempt->captcha !== strtolower($expectedCaptcha)
                => 'Invalid security code.',
            $attempt->password !== $attempt->passwordConfirmation
                => 'Password mismatch.',
            $attempt->password === '' || $attempt->login === ''
                => 'Please fill out all fields.',
            strlen($attempt->login) > 16
                => 'Username cannot be more than 16 characters long.',
            strlen($attempt->realname) > 50
                => 'Full name cannot be more than 50 characters long.',
            htmlspecialchars($attempt->login) !== $attempt->login
                => 'Username contains invalid characters.',
            $this->users->loginExists($attempt->login)
                => 'The user <strong>' . htmlspecialchars($attempt->login, ENT_QUOTES) . '</strong> already exists.',
            default => null,
        };

        if ($error !== null) {
            return $error;
        }

        $this->users->insert($attempt->login, $attempt->realname, $this->hash($attempt->password));

        return null;
    }

    public function updateSettings(string $login, SettingsUpdate $update): ?string
    {
        $error = match (true) {
            $update->password !== $update->passwordConfirmation
                => 'Password mismatch.',
            strlen($update->realname) > 50
                => 'Full name cannot be more than 50 characters long.',
            default => null,
        };

        if ($error !== null) {
            return $error;
        }

        $this->users->updateProfile($login, $update->realname);

        if ($update->password !== '') {
            $this->users->updatePassword($login, $this->hash($update->password));
        }

        return null;
    }

    private function hash(string $plainPassword): string
    {
        return $this->hasherFactory->getPasswordHasher(User::class)->hash($plainPassword);
    }
}
