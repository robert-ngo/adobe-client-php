<?php

declare(strict_types=1);

namespace Adobe\Client\Segmentation;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

/**
 * Client for Adobe Experience Platform Audience API.
 *
 * Provides methods for managing audiences in Adobe Experience Platform.
 * Audiences are collections of profiles that match specific criteria defined
 * through segment definitions or external sources.
 *
 * Base path: /data/core/ups/audiences
 */
final class AudiencesClient
{
    public function __construct(
        private readonly HttpClient $client,
    ) {
    }

    /**
     * List audiences.
     *
     * Retrieves a list of audiences with optional filtering and pagination.
     *
     * @param array<string,int|string> $options Query parameters: start, limit, sort, property, name, description, entityType
     * @return array<mixed>
     * @throws ApiException
     */
    public function listAudiences(array $options = []): array
    {
        $qs = http_build_query($options);
        $uri = '/data/core/ups/audiences' . ($qs !== '' ? ('?' . $qs) : '');
        $request = $this->client->createRequest('GET', $uri);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to list audiences', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Create a new audience.
     *
     * @param array<string,mixed> $payload Audience configuration including name, description, expression, etc.
     * @return array<mixed>
     * @throws ApiException
     */
    public function createAudience(array $payload): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/audiences', $payload);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to create audience', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Retrieve a specific audience by ID.
     *
     * @param string $audienceId The ID of the audience to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function getAudience(string $audienceId): array
    {
        $request = $this->client->createRequest('GET', '/data/core/ups/audiences/' . rawurlencode($audienceId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to retrieve audience', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Delete an audience.
     *
     * @param string $audienceId The ID of the audience to delete
     * @return void
     * @throws ApiException
     */
    public function deleteAudience(string $audienceId): void
    {
        $request = $this->client->createRequest('DELETE', '/data/core/ups/audiences/' . rawurlencode($audienceId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to delete audience', $response);
        }
    }

    /**
     * Patch an audience using JSON Patch operations.
     *
     * Updates one or more attributes of an audience using JSON Patch format.
     *
     * @param string $audienceId The ID of the audience to patch
     * @param array<int,array<string,mixed>> $operations JSON Patch operations (array of objects with op, path, value)
     * @return array<mixed>
     * @throws ApiException
     */
    public function patchAudience(string $audienceId, array $operations): array
    {
        $request = $this->client->createJsonRequest('PATCH', '/data/core/ups/audiences/' . rawurlencode($audienceId), $operations);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to patch audience', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Update an audience with a full replacement.
     *
     * @param string $audienceId The ID of the audience to update
     * @param array<string,mixed> $payload Complete audience configuration
     * @return array<mixed>
     * @throws ApiException
     */
    public function updateAudience(string $audienceId, array $payload): array
    {
        $request = $this->client->createJsonRequest('PUT', '/data/core/ups/audiences/' . rawurlencode($audienceId), $payload);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to update audience', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Bulk retrieve audiences by their IDs.
     *
     * @param array<int,string> $ids Array of audience IDs to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function bulkGetAudiences(array $ids): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/audiences/bulk-get', ['ids' => $ids]);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to bulk retrieve audiences', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}
