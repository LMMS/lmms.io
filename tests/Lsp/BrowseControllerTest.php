<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use PHPUnit\Framework\Attributes\DataProvider;

final class BrowseControllerTest extends AbstractLspWebTestCase
{
    public function testIndexReturns200(): void
    {
        $this->client->request('GET', '/lsp');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.lsp-title', 'Latest Uploads');
    }

    public function testIndexShowsLoginFormWhenUnauthenticated(): void
    {
        $this->client->request('GET', '/lsp');

        self::assertSelectorExists('form input[name="_username"]');
    }

    public function testBrowseByCategory(): void
    {
        $this->client->request('GET', '/lsp', ['action' => 'browse', 'category' => 'Projects']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.lsp-title', 'Projects');
    }

    public function testSearch(): void
    {
        $this->client->request('GET', '/lsp', ['search' => 'test']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.lsp-title', '"test"');
    }

    public function testBrowseByUser(): void
    {
        $this->client->request('GET', '/lsp', ['action' => 'browse', 'user' => 'nobody']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.lsp-title', 'nobody');
    }

    public function testShowFileRedirectsLegacyUrl(): void
    {
        $this->client->request('GET', '/lsp', ['action' => 'show', 'file' => '1']);

        self::assertResponseRedirects('/lsp/1', 301);
    }

    public function testLegacyIndexPhpUrlStillRoutes(): void
    {
        $this->client->request('GET', '/lsp/index.php', ['action' => 'show', 'file' => '1170']);

        self::assertResponseRedirects('/lsp/1170', 301);
    }

    #[DataProvider('legacyRedirectProvider')]
    public function testLegacyUrlRedirects(array $query, string $expectedTarget, int $expectedStatus = 301): void
    {
        $this->client->request('GET', '/lsp', $query);

        self::assertResponseRedirects($expectedTarget, $expectedStatus);
    }

    public static function legacyRedirectProvider(): array
    {
        return [
            'action=show'      => [['action' => 'show', 'file' => '42'], '/lsp/42'],
            'action=register'  => [['action' => 'register'], '/lsp/register'],
            'content=add'      => [['content' => 'add'], '/lsp/add'],
            'content=update'   => [['content' => 'update', 'file' => '7'], '/lsp/edit/7'],
            'content=delete'   => [['content' => 'delete', 'file' => '9'], '/lsp/delete/9'],
            'comment=add'      => [['comment' => 'add', 'file' => '3'], '/lsp/comment/3'],
            'account=settings' => [['account' => 'settings'], '/lsp/settings'],
            'rate=5&file=12'   => [['rate' => '5', 'file' => '12'], '/lsp/rate/12/5'],
            'rate=1&file=99'   => [['rate' => '1', 'file' => '99'], '/lsp/rate/99/1'],
        ];
    }

    public function testInvalidRateValueDoesNotRedirect(): void
    {
        $this->client->request('GET', '/lsp', ['rate' => '6', 'file' => '12']);

        self::assertResponseIsSuccessful();
    }

    public function testShowFileNotFound(): void
    {
        $this->client->request('GET', '/lsp/999999999');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('.alert-danger', 'File not found');
    }

    public function testShowFileReturns200(): void
    {
        $fileId = $this->createTestFile();

        $this->client->request('GET', '/lsp/' . $fileId);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table.table');

        $this->deleteTestFile($fileId);
    }
}
