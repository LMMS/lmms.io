<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Attribute\AsTwigFilter;

final class HtmlExtension
{
    #[AsTwigFilter('nest_headings', isSafe: ['html'])]
    public function nestHeadings(string $html, int $top): string
    {
        if (!preg_match_all('/<h([1-6])\b/i', $html, $m)) {
            return $html;
        }
        $levels = array_unique($m[1]);
        sort($levels);
        $map = [];
        foreach ($levels as $rank => $level) {
            $map[$level] = min(6, $top + $rank);
        }

        return preg_replace_callback(
            '/<(\/?)h([1-6])\b/i',
            fn ($m) => '<' . $m[1] . 'h' . ($map[$m[2]] ?? $m[2]),
            $html,
        );
    }
}
