<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

final class AccountControllerTest extends AbstractLspWebTestCase
{
    public function testRegisterFormShows(): void
    {
        $this->client->request('GET', '/lsp/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="login"]');
    }

    public function testRegisterLegacyRedirect(): void
    {
        $this->client->request('GET', '/lsp', ['action' => 'register']);

        self::assertResponseRedirects('/lsp/register', 301);
    }

    public function testRegisterPasswordMismatch(): void
    {
        $this->setSessionCaptcha('abcde');
        $this->client->request('POST', '/lsp/register', [
            'login'     => 'newuser',
            'realname'  => 'New User',
            'password'  => 'abc',
            'password2' => 'xyz',
            'captcha'   => 'abcde',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-danger', 'Password mismatch');
    }

    public function testRegisterDuplicateLogin(): void
    {
        $this->setSessionCaptcha('abcde');
        $this->client->request('POST', '/lsp/register', [
            'login'     => 'lsptest',
            'realname'  => 'Dup',
            'password'  => 'pass',
            'password2' => 'pass',
            'captcha'   => 'abcde',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-danger', 'already exists');
    }

    public function testRegisterSuccess(): void
    {
        $this->setSessionCaptcha('abcde');
        $this->client->request('POST', '/lsp/register', [
            'login'     => 'brandnewuser',
            'realname'  => 'Brand New',
            'password'  => 'secretpass',
            'password2' => 'secretpass',
            'captcha'   => 'abcde',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-success', 'successfully created');

        $this->connection->executeStatement(
            'DELETE FROM users WHERE login = :login',
            ['login' => 'brandnewuser'],
        );
    }

    public function testSettingsRequiresLogin(): void
    {
        $this->client->request('GET', '/lsp/settings');

        self::assertResponseRedirects('/lsp/login');
    }

    public function testSettingsFormShownWhenLoggedIn(): void
    {
        $this->client->request('GET', '/lsp/settings', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="realname"]');
    }

    public function testSettingsPasswordMismatch(): void
    {
        $this->client->request('POST', '/lsp/settings', [
            'settings'  => 'apply',
            'realname'  => 'LSP Test',
            'password'  => 'abc',
            'password2' => 'xyz',
        ], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-danger', 'Password mismatch');
    }

    public function testSettingsLegacyRedirect(): void
    {
        $this->client->request('GET', '/lsp', ['account' => 'settings']);

        self::assertResponseRedirects('/lsp/settings', 301);
    }

    public function testLoginPageReturns200(): void
    {
        $this->client->request('GET', '/lsp/login');

        self::assertResponseIsSuccessful();
    }

    public function testLoginWithValidCredentials(): void
    {
        $this->client->request('GET', '/lsp/login');
        $this->client->submitForm('Login', [
            '_username' => 'lsptest',
            '_password' => 'lsptestpass',
        ]);

        self::assertResponseRedirects('/lsp/');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request('GET', '/lsp/login');
        $this->client->submitForm('Login', [
            '_username' => 'lsptest',
            '_password' => 'wrongpassword',
        ]);

        self::assertResponseRedirects('/lsp/login');
    }

    public function testLoginThrottledAfterTooManyFailures(): void
    {
        // Use a throwaway username so we don't poison the rate-limiter cache
        // (keyed per-username) for other tests.
        $login = 'throttle_' . bin2hex(random_bytes(4));
        $this->connection->executeStatement(
            'INSERT INTO users (login, password, realname, is_admin, loginFailureCount) VALUES (:login, :sha1, :realname, 0, 0)',
            ['login' => $login, 'sha1' => sha1('correcthorse'), 'realname' => 'Throttle'],
        );

        try {
            for ($i = 0; $i < 5; $i++) {
                $this->client->request('GET', '/lsp/login');
                $this->client->submitForm('Login', [
                    '_username' => $login,
                    '_password' => 'wrongpassword',
                ]);
            }

            $this->client->request('GET', '/lsp/login');
            $this->client->submitForm('Login', [
                '_username' => $login,
                '_password' => 'correcthorse',
            ]);

            $this->client->followRedirect();
            self::assertSelectorTextContains('.alert-danger', 'Too many');
        } finally {
            $this->connection->executeStatement('DELETE FROM users WHERE login = :login', ['login' => $login]);
        }
    }

    public function testHttpBasicAuthWorksInTestEnv(): void
    {
        $this->client->request('GET', '/lsp/login', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();
    }
}
