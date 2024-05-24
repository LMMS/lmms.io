<?php

declare(strict_types=1);

namespace LMMS;

use Github\Client;
use Github\HttpClient\Builder;
use Http\Client\Common\Plugin\HeaderRemovePlugin;
use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Client\Common\Plugin\RequestMatcherPlugin;
use Http\Message\RequestMatcher\RequestMatcher;
use LMMS\HttpClientPlugin\MethodPlugin;
use LMMS\HttpClientPlugin\UriRecordPlugin;

/**
 * Extends the KNP Labs GitHub Client to provide URLs for artifact downloads,
 * rather than the artifacts themselves.
 */
class GithubDownloadClient extends Client
{
	public function __construct(Builder $httpClientBuilder = null, $apiVersion = null, $enterpriseUrl = null)
	{
		parent::__construct($httpClientBuilder, $apiVersion, $enterpriseUrl);

		$builder = $this->getHttpClientBuilder();

		// Convert requests to the artifact download endpoint to HEAD requests,
		// so we don't try to download artifacts server-side.
		$builder->addPlugin(
			new RequestMatcherPlugin(
				new RequestMatcher(path: 'actions/artifacts/\d+/zip$'),
				new MethodPlugin('HEAD')
			)
		);

		// The redirect plugin needs to be added after the method plugin, so the
		// HEAD method is preserved after redirecting to the artifact server.
		$builder->removePlugin(RedirectPlugin::class);
		$builder->addPlugin(new RedirectPlugin());

		// Stash the request URI in the response, so we can determine the final
		// redirect URI associated with a response.
		$builder->addPlugin(new UriRecordPlugin());
	}

	public function authenticate($tokenOrLogin, $password = null, $authMethod = null): void
	{
		parent::authenticate($tokenOrLogin, $password, $authMethod);

		// Strip the authorization header if redirected away from the GitHub API
		$this->getHttpClientBuilder()->addPlugin(
			new RequestMatcherPlugin(
				new RequestMatcher(host: '^api\.github\.com$'),
				null,
				new HeaderRemovePlugin(['Authorization'])
			)
		);
	}
}
