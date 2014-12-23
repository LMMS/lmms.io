<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/GitHubClient.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/FilesystemCache.php');

use Aptoma\Twig\Extension\MarkdownEngineInterface;

class GitHubMarkdownEngine implements MarkdownEngineInterface
{
	public function __construct()
	{
		$this->client = new \LMMS\GithubClient();
		$this->cache = new \LMMS\FilesystemCache('/tmp/github-markdown-cache');
	}

	public function transform($content)
	{
		if ($this->cache->has($content)) {
			return $this->cache->get($content);
		}

		try {
			$response = $this->client->api('markdown')->render($content, 'gfm', 'LMMS/lmms');
			$this->cache->set($content, $response);
			return $response;
		} catch (Exception $e) {
			return "<p>Failed rendering Markdown document.</p>";
		}
	}

	public function getName()
	{
		return 'KnpLabs\php-github-api';
	}

	private $client;
	private $cache;
}
