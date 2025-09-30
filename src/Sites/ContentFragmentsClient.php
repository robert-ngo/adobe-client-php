<?php

declare(strict_types=1);

namespace Adobe\Client\Sites;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

final class ContentFragmentsClient
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * List Content Fragments under a container path.
     *
     * @param array<string,int|string> $options limit, offset, model, recursive, search, sort
     * @return array<mixed>
     */
    public function list(string $containerPath = '/content/dam', array $options = []): array
    {
        $query = array_merge(['path' => $containerPath], $options);
        $qs = http_build_query($query);
        $request = $this->client->createRequest('GET', '/api/sites/v1/fragments' . ($qs !== '' ? ('?' . $qs) : ''));
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to list Content Fragments', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Create a Content Fragment.
     *
     * @param array<string,mixed> $payload Should include model, name, title, path, elements, etc.
     * @return array<mixed>
     */
    public function create(array $payload): array
    {
        $request = $this->client->createJsonRequest('POST', '/api/sites/v1/fragments', $payload);
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to create Content Fragment', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Get a Content Fragment by its repository path.
     *
     * @return array<mixed>
     */
    public function get(string $fragmentPath): array
    {
        $request = $this->client->createRequest('GET', '/api/sites/v1/fragments' . $this->encodePath($fragmentPath));
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to fetch Content Fragment', $response);
        }
        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Update (edit) a Content Fragment.
     *
     * @param array<string,mixed> $payload Elements or metadata to update
     * @return array<mixed>
     */
    public function update(string $fragmentPath, array $payload): array
    {
        $request = $this->client->createJsonRequest('PATCH', '/api/sites/v1/fragments' . $this->encodePath($fragmentPath), $payload);
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to update Content Fragment', $response);
        }
        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    public function delete(string $fragmentPath): void
    {
        $request = $this->client->createRequest('DELETE', '/api/sites/v1/fragments' . $this->encodePath($fragmentPath));
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to delete Content Fragment', $response);
        }
    }

    public function deleteAndUnpublish(string $fragmentPath): void
    {
        $request = $this->client->createRequest('POST', '/api/sites/v1/fragments' . $this->encodePath($fragmentPath) . '/delete-and-unpublish');
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to delete and unpublish Content Fragment', $response);
        }
    }

    /**
     * @return array<mixed>
     */
    public function getPreviewUrls(string $fragmentPath): array
    {
        $request = $this->client->createRequest('GET', '/api/sites/v1/fragments' . $this->encodePath($fragmentPath) . '/previews');
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to fetch preview URLs for Content Fragment', $response);
        }
        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    /**
     * Copy a Content Fragment to a destination path.
     *
     * @param array<string,mixed> $payload Should include destinationPath and optional name/title
     * @return array<mixed>
     */
    public function copy(string $fragmentPath, array $payload): array
    {
        $request = $this->client->createJsonRequest('POST', '/api/sites/v1/fragments' . $this->encodePath($fragmentPath) . '/copy', $payload);
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to copy Content Fragment', $response);
        }
        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    private function encodePath(string $path): string
    {
        return '/' . ltrim(str_replace('%2F', '/', rawurlencode($path)), '/');
    }
}


