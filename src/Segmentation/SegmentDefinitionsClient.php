<?php

declare(strict_types=1);

namespace Adobe\Client\Segmentation;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

/**
 * Client for Adobe Experience Platform Segment Definitions API.
 *
 * Provides methods for managing segment definitions in Adobe Experience Platform.
 * Segment definitions contain the logic and criteria for identifying specific
 * groups of profiles using Profile Query Language (PQL).
 *
 * Base path: /data/core/ups/segment/definitions
 */
final class SegmentDefinitionsClient
{
    public function __construct(
        private readonly HttpClient $client,
    ) {
    }

    /**
     * List segment definitions.
     *
     * Retrieves a list of segment definitions with optional filtering and pagination.
     *
     * @param array<string,int|string> $options Query parameters: start, limit, page, sort
     * @return array<mixed>
     * @throws ApiException
     */
    public function listSegmentDefinitions(array $options = []): array
    {
        $qs = http_build_query($options);
        $uri = '/data/core/ups/segment/definitions' . ($qs !== '' ? ('?' . $qs) : '');
        $request = $this->client->createRequest('GET', $uri);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to list segment definitions', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Create a new segment definition.
     *
     * @param array<string,mixed> $payload Segment definition including name, description, expression, schema, etc.
     * @return array<mixed>
     * @throws ApiException
     */
    public function createSegmentDefinition(array $payload): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/segment/definitions', $payload);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to create segment definition', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Retrieve a specific segment definition by ID.
     *
     * @param string $segmentId The ID of the segment definition to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function getSegmentDefinition(string $segmentId): array
    {
        $request = $this->client->createRequest('GET', '/data/core/ups/segment/definitions/' . rawurlencode($segmentId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to retrieve segment definition', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Delete a segment definition.
     *
     * @param string $segmentId The ID of the segment definition to delete
     * @return array<mixed>
     * @throws ApiException
     */
    public function deleteSegmentDefinition(string $segmentId): array
    {
        $request = $this->client->createRequest('DELETE', '/data/core/ups/segment/definitions/' . rawurlencode($segmentId));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to delete segment definition', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Patch a segment definition.
     *
     * Updates specific attributes of a segment definition.
     *
     * @param string $segmentId The ID of the segment definition to patch
     * @param array<string,mixed> $payload Partial segment definition with fields to update
     * @return array<mixed>
     * @throws ApiException
     */
    public function patchSegmentDefinition(string $segmentId, array $payload): array
    {
        $request = $this->client->createJsonRequest('PATCH', '/data/core/ups/segment/definitions/' . rawurlencode($segmentId), $payload);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to patch segment definition', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Bulk retrieve segment definitions by their IDs.
     *
     * @param array<int,string> $ids Array of segment definition IDs to retrieve
     * @return array<mixed>
     * @throws ApiException
     */
    public function bulkGetSegmentDefinitions(array $ids): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/segment/definitions/bulk-get', ['ids' => $ids]);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to bulk retrieve segment definitions', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Convert a segment definition between PQL formats.
     *
     * Converts a segment definition between pql/text and pql/json formats.
     *
     * @param array<string,mixed> $payload Conversion request with name and body
     * @return array<mixed>
     * @throws ApiException
     */
    public function convertSegmentDefinition(array $payload): array
    {
        $request = $this->client->createJsonRequest('POST', '/data/core/ups/segment/conversion', $payload);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to convert segment definition', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}
