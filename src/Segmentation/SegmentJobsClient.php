<?php

declare(strict_types=1);

namespace Adobe\Client\Segmentation;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

/**
 * Client for Adobe Experience Platform Segment Jobs API.
 *
 * Provides methods for managing segment jobs in Adobe Experience Platform.
 * Segment jobs evaluate segment definitions to generate audiences by processing
 * profiles that match the segment criteria.
 *
 * Base path: /data/core/ups/segment/jobs
 */
final class SegmentJobsClient
{
    public function __construct(
        private readonly HttpClient $client,
    ) {
    }

    /**
     * List segment jobs.
     *
     * Retrieves a list of segment jobs with optional filtering and pagination.
     *
     * @param array<string,int|string> $options Query parameters: snapshot.name, start, limit, status, sort, property
     * @return array<mixed>
     * @throws ApiException
     */
    public function listSegmentJobs(array $options = []): array
    {
        $qs = http_build_query($options);
        $uri = '/data/core/ups/segment/jobs' . ($qs !== '' ? ('?' . $qs) : '');
        $request = $this->client->createRequest('GET', $uri);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to list segment jobs', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Create a new segment job.
     *
     * Initiates a segment job to process segment definitions and generate audiences.
     *
     * @param array<int,array<string,mixed>> $segmentJobRequests Array of segment job request objects
     * @return array<mixed>
     * @throws ApiException
     */
    public function createSegmentJob(array $segmentJobRequests): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/segment/jobs', $segmentJobRequests);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to create segment job', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Retrieve a specific segment job by ID.
     *
     * @param string $segmentJobId The ID of the segment job to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function getSegmentJob(string $segmentJobId): array
    {
        $request = $this->client->createRequest('GET', '/data/core/ups/segment/jobs/' . rawurlencode($segmentJobId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to retrieve segment job', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Cancel a segment job.
     *
     * @param string $segmentJobId The ID of the segment job to cancel
     * @return void
     * @throws ApiException
     */
    public function cancelSegmentJob(string $segmentJobId): void
    {
        $request = $this->client->createRequest('DELETE', '/data/core/ups/segment/jobs/' . rawurlencode($segmentJobId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to cancel segment job', $response);
        }
    }

    /**
     * Bulk retrieve segment jobs by their IDs.
     *
     * @param array<int,string> $ids Array of segment job IDs to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function bulkGetSegmentJobs(array $ids): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/segment/jobs/bulk-get', ['ids' => $ids]);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to bulk retrieve segment jobs', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}
