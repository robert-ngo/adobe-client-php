<?php

declare(strict_types=1);

namespace Adobe\Client\Core;

final class SdkConfig
{
    private string $baseUri;
    private string $userAgent;
    /** @var array<string,string> */
    private array $defaultHeaders;

    /**
     * @param array<string,string> $defaultHeaders
     */
    public function __construct(string $baseUri, string $userAgent = 'adobe-client-php/0.1.0', array $defaultHeaders = [])
    {
        $this->baseUri = rtrim($baseUri, '/');
        $this->userAgent = $userAgent;
        $this->defaultHeaders = $defaultHeaders;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return array<string,string>
     */
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }
}


