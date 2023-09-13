<?php
namespace LMMS;

use Github\Client;
use Github\HttpClient\Builder;
use Http\Client\Common\Plugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Extends the KNP Labs GitHub Client to provide URLs for artifact downloads,
 * rather than the artifacts themselves.
 */
class GithubDownloadClient extends Client
{
	public function __construct(Builder $httpClientBuilder = null, $apiVersion = null, $enterpriseUrl = null)
	{
		parent::__construct($httpClientBuilder, $apiVersion, $enterpriseUrl);
		$this->getHttpClientBuilder()->addPlugin(new class implements Plugin {
			public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
			{
				return $next($request)->then(function(ResponseInterface $response): ResponseInterface {
					if ($response->getStatusCode() === 302) {
						$location = $response->getHeader('Location')[0];
						if (str_starts_with($location, 'https://pipelines') && str_contains($location, '.actions.githubusercontent.com')) {
							$body = Psr17FactoryDiscovery::findStreamFactory()->createStream($location);
							$body->rewind();
							return $response
								->withStatus(200)
								->withHeader('Content-Type', 'text/plain')
								->withoutHeader('Location')
								->withBody($body);
						}
					}
					return $response;
				});
			}
		});
	}
}
