<?php

use Aptoma\Twig\Extension\MarkdownEngineInterface;

class GitHubMarkdownEngine implements MarkdownEngineInterface
{
	public function __construct()
	{
		$this->client = new \Github\Client(
			new \Github\HttpClient\CachedHttpClient(['cache_dir' => '/tmp/github-api-cache'])
		);
	}

	public function transform($content)
	{
		try {
			return $this->client->api('markdown')->render($content, 'gfm', 'LMMS/lmms');
		} catch (Exception $e) {
			return "<p>Failed rendering Markdown document.</p>";
		}
	}

	public function getName()
	{
		return 'KnpLabs\php-github-api';
	}

	private $client;
}
