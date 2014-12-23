<?php
namespace LMMS;

use Github\HttpClient\HttpClientInterface;
use Github\HttpClient\CachedHttpClient;

require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/feed/json_common.php');

// This CachedHttpClient subclass catches exceptions of requests and tries to return a cache object in case of failure.
class SafeCachedHttpClient extends \Github\HttpClient\CachedHttpClient
{
	/**
	 * @param int $maxage Sets the maximum age of a cache object in seconds.
	 *                    If $maxage is =0, the cache is only used if the remote resource has not been modified (403) or
	 *                                      if the HTTP request failed.
	 *                    If $maxage is >0, the cache is always used if it's younger than $maxage seconds.
	 *                    If $maxage is <0, the cache is always used, no matter its age.
	 */
	public function __construct(array $options = array(), $maxage=0)
	{
		parent::__construct($options);
		$this->maxage = $maxage;
	}

	public function request($path, $body = null, $httpMethod = 'GET', array $headers = array(), array $options = array())
	{
		$cache = $this->getCache();
		$has = $cache->has($path);

		if ($has && $this->maxage != 0) {
			if ($this->maxage < 0 || (time () - $cache->getModifiedSince($path) <= $this->maxage)) {
				return $cache->get($path);
			}
		}


		try {
			return parent::request($path, $body, $httpMethod, $headers, $options);
		} catch (Exception $e) {
			if ($has) {
				return $cache->get($path);
			} else {
				throw $e;
			}
		}
	}

	private $maxage;
}

class GitHubClient extends \Github\Client
{
	public function __construct(HttpClientInterface $httpClient = null)
	{
		parent::__construct($httpClient);
		$client_id = get_base64_secret('GITHUB_CLIENT_ID');
		$client_secret = get_base64_secret('GITHUB_CLIENT_SECRET');
		$this->authenticate($client_id, $client_secret, \Github\Client::AUTH_URL_CLIENT_ID);
	}
}
