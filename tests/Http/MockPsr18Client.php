<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockPsr18Client implements ClientInterface
{
    /** @var callable(RequestInterface):ResponseInterface */
    private $handler;

    /**
     * @param callable(RequestInterface):ResponseInterface $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $handler = $this->handler;
        return $handler($request);
    }
}


