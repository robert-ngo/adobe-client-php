<?php

declare(strict_types=1);

namespace Adobe\Client\Auth;

use Adobe\Client\Core\AuthProvider;
use Psr\Http\Message\RequestInterface;

final class BearerTokenProvider implements AuthProvider
{
    private string $accessToken;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->accessToken);
    }
}


