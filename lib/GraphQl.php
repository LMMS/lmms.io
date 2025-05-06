<?php
namespace LMMS;

use Github\Client;
use LMMS\HttpClientPlugin\UriRecordPlugin;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GraphQl
{
	public function __construct(
		private Client $client,
		private string $owner,
		private string $repo,
		private UrlGeneratorInterface $router
	)
	{ }

	public function executeQuery(string $query): array
    	{
    		$query = str_replace("%owner%", $this->owner, $query);
    		$query = str_replace("%repo%", $this->repo, $query);

    		return $this->client->api('graphql')->execute($query);
    	}
}
