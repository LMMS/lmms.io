<?php

declare(strict_types=1);

namespace App\Tests\Locale;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EnabledLocalesTest extends KernelTestCase
{
    private const UNLISTED = ['hi_IN'];

    public function testEveryTranslationIsEnabled(): void
    {
        $container = static::getContainer();
        $files = glob($container->getParameter('translator.default_path').'/messages.*.xlf');
        $translated = array_map(static fn (string $file): string => explode('.', basename($file))[1], $files);

        self::assertEqualsCanonicalizing(
            array_values(array_diff($translated, self::UNLISTED)),
            $container->getParameter('kernel.enabled_locales'),
            'Add new translations to framework.enabled_locales in config/packages/translation.yaml',
        );
    }
}
