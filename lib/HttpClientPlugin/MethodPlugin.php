<?php

declare(strict_types=1);

namespace LMMS\HttpClientPlugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * Set the method for the request.
 */
class MethodPlugin implements Plugin
{
	public function __construct(private string $method)
	{ }

	public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
	{
		return $next($request->withMethod($this->method));
	}
}
