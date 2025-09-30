<?php

declare(strict_types=1);

namespace Adobe\Client\Sites;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

final class SitesClient
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * List sites/pages. Endpoint path may vary per AEM setup; this is a placeholder.
     *
     * @return array<mixed>
     */
    public function listPages(string $path = '/content'): array
    {
        $request = $this->client->createRequest('GET', '/api/sites/v1/pages?path=' . rawurlencode($path));
        $response = $this->client->send($request);

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new ApiException('Failed to list AEM pages; status ' . $status, $response);
        }

        $body = (string) $response->getBody();
        /** @var array<mixed> $data */
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}


