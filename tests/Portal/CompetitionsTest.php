<?php

declare(strict_types=1);

namespace App\Tests\Portal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CompetitionsTest extends WebTestCase
{
    public function testDescriptionsRenderAsMarkdownNotCodeBlocks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/competitions');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.comp-desc h4');
        self::assertSelectorNotExists('.comp-desc pre');
    }
}
