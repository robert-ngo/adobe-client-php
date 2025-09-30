<?php

declare(strict_types=1);

namespace Adobe\Client\Core;

use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Psr\Http\Client\ClientExceptionInterface as Psr18ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Thin wrapper around a PSR-18 HTTP client that applies base configuration
 * and authentication to each request.
 */
final class HttpClient
{
    private Psr18ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private SdkConfig $config;
    private ?AuthProvider $authProvider;

    public function __construct(
        Psr18ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SdkConfig $config,
        ?AuthProvider $authProvider = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->config = $config;
        $this->authProvider = $authProvider;
    }

    public function createRequest(string $method, string $uri): RequestInterface
    {
        $url = rtrim($this->config->getBaseUri(), '/') . '/' . ltrim($uri, '/');
        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', $this->config->getUserAgent());

        foreach ($this->config->getDefaultHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($this->authProvider !== null) {
            $request = $this->authProvider->authenticate($request);
        }

        return $request;
    }

    public function createJsonRequest(string $method, string $uri, array $payload): RequestInterface
    {
        $request = $this->createRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json');

        $body = $this->streamFactory->createStream(json_encode($payload, JSON_THROW_ON_ERROR));
        return $request->withBody($body);
    }

    /**
     * @throws Psr18ClientExceptionInterface
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }
}


