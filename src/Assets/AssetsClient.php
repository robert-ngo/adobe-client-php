<?php

declare(strict_types=1);

namespace Adobe\Client\Assets;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;
use Psr\Http\Message\StreamInterface;

final class AssetsClient
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Upload an asset to AEM DAM.
     *
     * @param array<string,string> $metadata
     */
    public function upload(string $targetPath, StreamInterface $content, string $contentType, array $metadata = []): void
    {
        $request = $this->client->createRequest('PUT', '/api/assets/v1' . $targetPath)
            ->withHeader('Content-Type', $contentType)
            ->withBody($content);

        foreach ($metadata as $name => $value) {
            $request = $request->withHeader('X-Meta-' . $name, $value);
        }

        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to upload asset', $response);
        }
    }

    /**
     * Retrieve asset metadata or content reference.
     *
     * @return array<mixed>
     */
    public function get(string $assetPath): array
    {
        $request = $this->client->createRequest('GET', '/api/assets/v1' . $assetPath);
        $response = $this->client->send($request);
        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to retrieve asset', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}


