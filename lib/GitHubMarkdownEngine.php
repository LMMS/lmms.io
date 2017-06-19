<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/FilesystemCache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../lib/GitHubClient.php');

use Aptoma\Twig\Extension\MarkdownEngineInterface;

class GitHubMarkdownEngine implements MarkdownEngineInterface
{
	public function __construct()
	{
		$this->client = new \LMMS\GitHubClient();
		$this->parsedown = new ParsedownExtra();
		$this->cache = new \LMMS\FilesystemCache('/tmp/github-markdown-cache');
	}

	public function transform($content)
	{
		if ($this->cache->has($content)) {
			return $this->cache->get($content);
		}

		if (substr_compare('|GH|', $content, 0, 4) === 0) {
			$content = substr($content, 4, strlen($content));
			try {
				$response = $this->client->api('markdown')->render($content, 'gfm', 'LMMS/lmms');
				$this->cache->set($content, $response);
				return $response;
			} catch (Exception $e) {
				return transform($content);
			}
		}

		$response = $this->parsedown->text($content);
		$this->cache->set($content, $response);
		return $response;
	}

	public function getName()
	{
		return 'KnpLabs\php-github-api';
	}

	private $client;
	private $cache;
}
