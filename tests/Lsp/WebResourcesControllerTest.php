<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

final class WebResourcesControllerTest extends AbstractLspWebTestCase
{
    public function testWebResourcesFallbackXml(): void
    {
        $this->client->request('GET', '/lsp/web_resources.php');

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/xml', (string) $this->client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('<error>', (string) $this->client->getResponse()->getContent());
    }

    public function testWebResourcesIndexXml(): void
    {
        $this->client->request('GET', '/lsp/web_resources.php', ['download' => 'index']);

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/xml', (string) $this->client->getResponse()->headers->get('Content-Type'));
        $body = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('<webresources>', $body);
        self::assertStringContainsString('</webresources>', $body);
    }

    public function testWebResourcesUnknownHashReturns404(): void
    {
        $this->client->request('GET', '/lsp/web_resources.php', [
            'download' => 'resource',
            'id'       => 'definitelynotahash',
        ]);

        self::assertResponseStatusCodeSame(404);
    }
}
