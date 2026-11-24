<?php

declare(strict_types=1);

namespace App\Locale;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\String\u;

final class AvailableLocales
{
    /**
     * Symfony matches zh-HK and the like by their bare "zh", which picks zh_CN. Listed after the
     * real locales, these let the script or region decide while a bare "zh" still gets zh_CN.
     */
    private const ALIASES = [
        'zh_Hans' => 'zh_CN',
        'zh_Hant' => 'zh_TW',
        'zh_HK' => 'zh_TW',
        'zh_MO' => 'zh_TW',
    ];

    /**
     * @param list<string> $locales
     */
    public function __construct(
        #[Autowire('%kernel.enabled_locales%')] private readonly array $locales,
        #[Autowire('%kernel.default_locale%')] private readonly string $defaultLocale,
    ) {}

    public function has(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }

    /**
     * @return array<string, string>
     */
    public function names(): array
    {
        $names = [];
        foreach ($this->locales as $locale) {
            $names[$locale] = u(\Locale::getDisplayName($locale, $locale))->title()->toString();
        }

        return $names;
    }

    public function negotiate(Request $request): string
    {
        $locale = $request->getPreferredLanguage([$this->defaultLocale, ...$this->locales, ...array_keys(self::ALIASES)]);

        return self::ALIASES[$locale] ?? $locale;
    }
}
