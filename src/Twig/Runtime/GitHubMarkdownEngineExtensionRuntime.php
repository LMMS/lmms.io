<?php

namespace App\Twig\Runtime;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Twig\Extension\RuntimeExtensionInterface;

class GitHubMarkdownEngineExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly HttpClientInterface $githubClient)
    {
        $this->cache = new FilesystemAdapter();
        $this->fallback_render = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function render(string $content): string
    {
        $this->content = $content;
        return $this->cache->get(hash('sha256', $content), function (ItemInterface $item) {
            $content = $this->content;
            try {
                return $this->githubClient->request('POST', '/markdown', ['json' => [
                    'text'    => $content,
                    'mode'    => 'gfm',
                    'context' => 'LMMS/lmms',
                ]])->getContent();
            } catch (\Throwable $e) {
                error_log($e);
                return $this->fallback_render->convert($content);
            }
        });
    }

    private FilesystemAdapter $cache;
    private string $content;
    private GithubFlavoredMarkdownConverter $fallback_render;
}
