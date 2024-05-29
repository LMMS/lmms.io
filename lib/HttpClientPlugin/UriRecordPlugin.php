<?php

declare(strict_types=1);

namespace LMMS\HttpClientPlugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Store the request URI in a header on the response.
 */
class UriRecordPlugin implements Plugin
{
	public const HEADER_NAME = 'X-LMMS-Request-Uri';

	public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
	{
		$uri = (string) $request->getUri();

		return $next($request)->then(function (ResponseInterface $response) use ($uri): ResponseInterface {
			return $response->withHeader(self::HEADER_NAME, $uri);
		});
	}
}
