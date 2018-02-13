<?php
namespace LMMS;

require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/feed/json_common.php');

use Cache\Adapter\Apcu\ApcuCachePool;

class GitHubClient extends \Github\Client
{
	public function __construct(HttpClientInterface $httpClient = null)
	{
		parent::__construct($httpClient);
		$client_id = get_base64_secret('GITHUB_CLIENT_ID');
		$client_secret = get_base64_secret('GITHUB_CLIENT_SECRET');
		$cache = new ApcuCachePool();
		$this->addCache($cache);
		$this->authenticate($client_id, $client_secret, \Github\Client::AUTH_URL_CLIENT_ID);
	}
}
