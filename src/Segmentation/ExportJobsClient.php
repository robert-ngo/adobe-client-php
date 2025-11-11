<?php

declare(strict_types=1);

namespace Adobe\Client\Segmentation;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

/**
 * Client for Adobe Experience Platform Export Jobs API.
 *
 * Provides methods for managing export jobs in Adobe Experience Platform.
 * Export jobs are used to export audience members to datasets for further
 * processing or integration with external systems.
 *
 * Base path: /data/core/ups/export/jobs
 */
final class ExportJobsClient
{
    public function __construct(
        private readonly HttpClient $client,
    ) {
    }

    /**
     * List export jobs.
     *
     * Retrieves a list of export jobs with optional filtering and pagination.
     *
     * @param array<string,int|string> $options Query parameters: limit, offset, status
     * @return array<mixed>
     * @throws ApiException
     */
    public function listExportJobs(array $options = []): array
    {
        $qs = http_build_query($options);
        $uri = '/data/core/ups/export/jobs' . ($qs !== '' ? ('?' . $qs) : '');
        $request = $this->client->createRequest('GET', $uri);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to list export jobs', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Create a new export job.
     *
     * Initiates an asynchronous export job to persist audience members to a dataset.
     *
     * @param array<string,mixed> $payload Export job configuration including fields, mergePolicy, filter, etc.
     * @return array<mixed>
     * @throws ApiException
     */
    public function createExportJob(array $payload): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/export/jobs', $payload);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to create export job', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Retrieve a specific export job by ID.
     *
     * @param string $exportJobId The ID of the export job to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function getExportJob(string $exportJobId): array
    {
        $request = $this->client->createRequest('GET', '/data/core/ups/export/jobs/' . rawurlencode($exportJobId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to retrieve export job', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Cancel an export job.
     *
     * @param string $exportJobId The ID of the export job to cancel
     * @return array<mixed>
     * @throws ApiException
     */
    public function cancelExportJob(string $exportJobId): array
    {
        $request = $this->client->createRequest('DELETE', '/data/core/ups/export/jobs/' . rawurlencode($exportJobId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to cancel export job', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}
