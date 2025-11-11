<?php

declare(strict_types=1);

namespace Adobe\Client;

use Adobe\Client\Assets\AssetsClient;
use Adobe\Client\Core\AuthProvider;
use Adobe\Client\Core\HttpClient;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Segmentation\AudiencesClient;
use Adobe\Client\Segmentation\ExportJobsClient;
use Adobe\Client\Segmentation\SegmentDefinitionsClient;
use Adobe\Client\Segmentation\SegmentJobsClient;
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
    private AudiencesClient $audiences;
    private ExportJobsClient $exportJobs;
    private SegmentDefinitionsClient $segmentDefinitions;
    private SegmentJobsClient $segmentJobs;

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
        $this->audiences = new AudiencesClient($this->http);
        $this->exportJobs = new ExportJobsClient($this->http);
        $this->segmentDefinitions = new SegmentDefinitionsClient($this->http);
        $this->segmentJobs = new SegmentJobsClient($this->http);
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

    public function audiences(): AudiencesClient
    {
        return $this->audiences;
    }

    public function exportJobs(): ExportJobsClient
    {
        return $this->exportJobs;
    }

    public function segmentDefinitions(): SegmentDefinitionsClient
    {
        return $this->segmentDefinitions;
    }

    public function segmentJobs(): SegmentJobsClient
    {
        return $this->segmentJobs;
    }
}


