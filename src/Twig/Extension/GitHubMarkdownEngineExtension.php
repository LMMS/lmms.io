<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\GitHubMarkdownEngineExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GitHubMarkdownEngineExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('gfm_to_html', [GitHubMarkdownEngineExtensionRuntime::class, 'render'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('gfm_to_html', [GitHubMarkdownEngineExtensionRuntime::class, 'render'], ['is_safe' => ['html']]),
        ];
    }
}
