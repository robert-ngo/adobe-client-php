<?php

declare(strict_types=1);

namespace Adobe\Client;

use Adobe\Client\Assets\AssetsClient;
use Adobe\Client\Core\AuthProvider;
use Adobe\Client\Core\HttpClient;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Sites\SitesClient;
use Adobe\Client\Sites\ContentFragmentsClient;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Sdk
{
    private HttpClient $http;
    private SitesClient $sites;
    private AssetsClient $assets;
    private ContentFragmentsClient $contentFragments;

    public function __construct(
        Psr18ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SdkConfig $config,
        ?AuthProvider $authProvider = null
    ) {
        $this->http = new HttpClient($httpClient, $requestFactory, $streamFactory, $config, $authProvider);
        $this->sites = new SitesClient($this->http);
        $this->assets = new AssetsClient($this->http);
        $this->contentFragments = new ContentFragmentsClient($this->http);
    }

    public function sites(): SitesClient
    {
        return $this->sites;
    }

    public function assets(): AssetsClient
    {
        return $this->assets;
    }

    public function contentFragments(): ContentFragmentsClient
    {
        return $this->contentFragments;
    }
}


