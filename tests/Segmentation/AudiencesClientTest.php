<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\Segmentation;

use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient as CoreHttpClient;
use Adobe\Client\Exceptions\ApiException;
use Adobe\Client\Segmentation\AudiencesClient;
use Adobe\Client\Tests\Http\MockPsr18Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class AudiencesClientTest extends TestCase
{
    private function makeClient(callable $handler): AudiencesClient
    {
        $psr18 = new MockPsr18Client($handler);
        $factory = new HttpFactory();
        $core = new CoreHttpClient($psr18, $factory, $factory, new SdkConfig('https://platform.adobe.io'));
        return new AudiencesClient($core);
    }

    public function testListAudiences(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences', (string) $request->getUri());
            $this->assertStringContainsString('start=0', (string) $request->getUri());
            $this->assertStringContainsString('limit=20', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'audiences' => [],
                'page' => ['totalCount' => 0],
            ]));
        });

        $result = $client->listAudiences(['start' => 0, 'limit' => 20]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('audiences', $result);
    }

    public function testListAudiencesWithFilters(): void
    {
        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString('sort=updateTime%3Adesc', $uri);
            $this->assertStringContainsString('name=test', $uri);
            $this->assertStringContainsString('entityType=segment', $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['audiences' => []]));
        });

        $result = $client->listAudiences([
            'sort' => 'updateTime:desc',
            'name' => 'test',
            'entityType' => 'segment',
        ]);
        $this->assertIsArray($result);
    }

    public function testCreateAudience(): void
    {
        $payload = [
            'name' => 'Test Audience',
            'description' => 'Test audience description',
            'type' => 'SegmentDefinition',
            'expression' => [
                'type' => 'PQL',
                'format' => 'pql/text',
                'value' => 'workAddress.country = "US"',
            ],
        ];

        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences', (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(201, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'test-audience-id',
                'name' => 'Test Audience',
            ]));
        });

        $result = $client->createAudience($payload);
        $this->assertSame('test-audience-id', $result['id']);
        $this->assertSame('Test Audience', $result['name']);
    }

    public function testGetAudience(): void
    {
        $audienceId = '60ccea95-1435-4180-97a5-58af4aa285ab';

        $client = $this->makeClient(function ($request) use ($audienceId) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences/' . $audienceId, (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $audienceId,
                'name' => 'Test Audience',
            ]));
        });

        $result = $client->getAudience($audienceId);
        $this->assertSame($audienceId, $result['id']);
    }

    public function testDeleteAudience(): void
    {
        $audienceId = '60ccea95-1435-4180-97a5-58af4aa285ab';

        $client = $this->makeClient(function ($request) use ($audienceId) {
            $this->assertSame('DELETE', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences/' . $audienceId, (string) $request->getUri());
            return new Response(204);
        });

        $client->deleteAudience($audienceId);
        $this->assertTrue(true);
    }

    public function testDeleteAudienceThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to delete audience');

        $client = $this->makeClient(function ($request) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'status' => 404,
                'title' => 'Audience not found',
            ]));
        });

        $client->deleteAudience('non-existent-id');
    }

    public function testPatchAudience(): void
    {
        $audienceId = '60ccea95-1435-4180-97a5-58af4aa285ab';
        $operations = [
            ['op' => 'replace', 'path' => '/name', 'value' => 'Updated Name'],
            ['op' => 'replace', 'path' => '/description', 'value' => 'Updated Description'],
        ];

        $client = $this->makeClient(function ($request) use ($audienceId, $operations) {
            $this->assertSame('PATCH', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences/' . $audienceId, (string) $request->getUri());
            $this->assertSame(json_encode($operations), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $audienceId,
                'name' => 'Updated Name',
            ]));
        });

        $result = $client->patchAudience($audienceId, $operations);
        $this->assertSame('Updated Name', $result['name']);
    }

    public function testUpdateAudience(): void
    {
        $audienceId = '60ccea95-1435-4180-97a5-58af4aa285ab';
        $payload = [
            'name' => 'Completely Updated Audience',
            'description' => 'New description',
            'expression' => [
                'type' => 'PQL',
                'format' => 'pql/text',
                'value' => 'workAddress.country = "CA"',
            ],
        ];

        $client = $this->makeClient(function ($request) use ($audienceId, $payload) {
            $this->assertSame('PUT', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences/' . $audienceId, (string) $request->getUri());
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $audienceId,
                'name' => $payload['name'],
            ]));
        });

        $result = $client->updateAudience($audienceId, $payload);
        $this->assertSame($payload['name'], $result['name']);
    }

    public function testBulkGetAudiences(): void
    {
        $ids = [
            '60ccea95-1435-4180-97a5-58af4aa285ab',
            '70ccea95-1435-4180-97a5-58af4aa285ac',
        ];

        $client = $this->makeClient(function ($request) use ($ids) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/audiences/bulk-get', (string) $request->getUri());
            $this->assertSame(json_encode(['ids' => $ids]), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'results' => [
                    ['id' => $ids[0], 'name' => 'Audience 1'],
                    ['id' => $ids[1], 'name' => 'Audience 2'],
                ],
            ]));
        });

        $result = $client->bulkGetAudiences($ids);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
    }

    public function testListAudiencesThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to list audiences');

        $client = $this->makeClient(function ($request) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'status' => 500,
                'title' => 'Internal server error',
            ]));
        });

        $client->listAudiences();
    }

    public function testCreateAudienceThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to create audience');

        $client = $this->makeClient(function ($request) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'status' => 400,
                'title' => 'Bad request',
            ]));
        });

        $client->createAudience(['name' => 'Test']);
    }

    public function testAudienceIdIsUrlEncoded(): void
    {
        $audienceId = 'test id with spaces';

        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString(rawurlencode('test id with spaces'), $uri);
            $this->assertStringNotContainsString(' ', $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 'test id with spaces']));
        });

        $client->getAudience($audienceId);
    }
}
