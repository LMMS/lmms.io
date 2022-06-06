<?php
namespace LMMS;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Michelf\MarkdownExtra;

use Aptoma\Twig\Extension\MarkdownEngineInterface;

class GitHubMarkdownEngine implements MarkdownEngineInterface
{
	public function __construct()
	{
		$this->cache = new FilesystemAdapter();
		$this->client = new \Github\Client();
		$this->client->addCache($this->cache, [
			'default_ttl' => 7200
		]);
	}

	public function transform($content): string
	{
		$this->content = $content;
		$response = $this->cache->get(hash('sha256', $content), function (ItemInterface $item) {
			$content = $this->content;
			if (substr_compare('|GH|', $content, 0, 4) === 0) {
				$content = substr($content, 4, strlen($content));
				try {
					$response = $this->client->api('markdown')->render($content, 'gfm', 'LMMS/lmms');
					return $response;
				} catch (Exception $e) {
					error_log($e);
					return MarkdownExtra::defaultTransform($content);
				}
			}
			return MarkdownExtra::defaultTransform($content);
		});
		return $response;
	}

	public function getName(): string
	{
		return 'LMMS\GitHubMarkdown';
	}

	private $client;
	private $cache;
}
