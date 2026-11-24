<?php

declare(strict_types=1);

namespace App\Tests\GitHub;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FakeGitHub
{
    public array $requests = [];

    /**
     * @param array<string, array|MockResponse> $routes JSON bodies or whole responses, keyed by "METHOD /path";
     *                                                  anything else answers 404
     */
    public function __construct(public array $routes)
    {
    }

    public function client(): MockHttpClient
    {
        return new MockHttpClient(function (string $method, string $url) {
            $route = $method . ' ' . parse_url($url, PHP_URL_PATH);
            $this->requests[] = $route;
            $response = $this->routes[$route] ?? new JsonMockResponse(['message' => 'Not Found'], ['http_code' => 404]);

            return \is_array($response) ? new JsonMockResponse($response) : $response;
        }, 'https://api.github.com');
    }
}
