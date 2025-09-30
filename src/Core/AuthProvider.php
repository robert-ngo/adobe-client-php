<?php

declare(strict_types=1);

namespace Adobe\Client\Core;

use Psr\Http\Message\RequestInterface;

interface AuthProvider
{
    public function authenticate(RequestInterface $request): RequestInterface;
}


