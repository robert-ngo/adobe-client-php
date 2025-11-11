<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\Segmentation;

use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient as CoreHttpClient;
use Adobe\Client\Exceptions\ApiException;
use Adobe\Client\Segmentation\SegmentDefinitionsClient;
use Adobe\Client\Tests\Http\MockPsr18Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SegmentDefinitionsClientTest extends TestCase
{
    private function makeClient(callable $handler): SegmentDefinitionsClient
    {
        $psr18 = new MockPsr18Client($handler);
        $factory = new HttpFactory();
        $core = new CoreHttpClient($psr18, $factory, $factory, new SdkConfig('https://platform.adobe.io'));
        return new SegmentDefinitionsClient($core);
    }

    public function testListSegmentDefinitions(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/definitions', (string) $request->getUri());
            $this->assertStringContainsString('start=0', (string) $request->getUri());
            $this->assertStringContainsString('limit=50', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'segments' => [],
                'page' => ['totalCount' => 0],
            ]));
        });

        $result = $client->listSegmentDefinitions(['start' => 0, 'limit' => 50]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('segments', $result);
    }

    public function testListSegmentDefinitionsWithSorting(): void
    {
        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString('sort=updateTime%3Adesc', $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['segments' => []]));
        });

        $result = $client->listSegmentDefinitions(['sort' => 'updateTime:desc']);
        $this->assertIsArray($result);
    }

    public function testCreateSegmentDefinition(): void
    {
        $payload = [
            'name' => 'Test Segment',
            'description' => 'Test segment description',
            'expression' => [
                'type' => 'PQL',
                'format' => 'pql/text',
                'value' => 'workAddress.country = "US"',
            ],
            'schema' => [
                'name' => '_xdm.context.profile',
            ],
            'ttlInDays' => 60,
        ];

        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/definitions', (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'segment-id',
                'name' => 'Test Segment',
            ]));
        });

        $result = $client->createSegmentDefinition($payload);
        $this->assertSame('segment-id', $result['id']);
        $this->assertSame('Test Segment', $result['name']);
    }

    public function testGetSegmentDefinition(): void
    {
        $segmentId = 'segment-id-123';

        $client = $this->makeClient(function ($request) use ($segmentId) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/definitions/' . $segmentId, (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $segmentId,
                'name' => 'Test Segment',
            ]));
        });

        $result = $client->getSegmentDefinition($segmentId);
        $this->assertSame($segmentId, $result['id']);
    }

    public function testDeleteSegmentDefinition(): void
    {
        $segmentId = 'segment-id-123';

        $client = $this->makeClient(function ($request) use ($segmentId) {
            $this->assertSame('DELETE', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/definitions/' . $segmentId, (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'message' => 'Segment deleted',
            ]));
        });

        $result = $client->deleteSegmentDefinition($segmentId);
        $this->assertArrayHasKey('message', $result);
    }

    public function testPatchSegmentDefinition(): void
    {
        $segmentId = 'segment-id-123';
        $payload = [
            'name' => 'Updated Segment Name',
            'description' => 'Updated description',
        ];

        $client = $this->makeClient(function ($request) use ($segmentId, $payload) {
            $this->assertSame('PATCH', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/definitions/' . $segmentId, (string) $request->getUri());
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $segmentId,
                'name' => 'Updated Segment Name',
            ]));
        });

        $result = $client->patchSegmentDefinition($segmentId, $payload);
        $this->assertSame('Updated Segment Name', $result['name']);
    }

    public function testBulkGetSegmentDefinitions(): void
    {
        $ids = ['segment-id-1', 'segment-id-2'];

        $client = $this->makeClient(function ($request) use ($ids) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/definitions/bulk-get', (string) $request->getUri());
            $this->assertSame(json_encode(['ids' => $ids]), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'results' => [
                    ['id' => $ids[0], 'name' => 'Segment 1'],
                    ['id' => $ids[1], 'name' => 'Segment 2'],
                ],
            ]));
        });

        $result = $client->bulkGetSegmentDefinitions($ids);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
    }

    public function testConvertSegmentDefinition(): void
    {
        $payload = [
            'name' => 'Test Segment',
            'body' => [
                'xdmEntity' => [
                    'workAddress' => [
                        'country' => 'US',
                    ],
                ],
            ],
        ];

        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/conversion', (string) $request->getUri());
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'name' => 'Test Segment',
                'expression' => [
                    'type' => 'PQL',
                    'format' => 'pql/text',
                    'value' => 'workAddress.country = "US"',
                ],
            ]));
        });

        $result = $client->convertSegmentDefinition($payload);
        $this->assertArrayHasKey('expression', $result);
    }

    public function testListSegmentDefinitionsThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to list segment definitions');

        $client = $this->makeClient(function ($request) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'status' => 500,
                'title' => 'Internal server error',
            ]));
        });

        $client->listSegmentDefinitions();
    }

    public function testCreateSegmentDefinitionThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to create segment definition');

        $client = $this->makeClient(function ($request) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'status' => 400,
                'title' => 'Bad request',
            ]));
        });

        $client->createSegmentDefinition(['name' => 'Test']);
    }

    public function testGetSegmentDefinitionThrowsOnNotFound(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to retrieve segment definition');

        $client = $this->makeClient(function ($request) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'status' => 404,
                'title' => 'Segment not found',
            ]));
        });

        $client->getSegmentDefinition('non-existent-id');
    }

    public function testSegmentIdIsUrlEncoded(): void
    {
        $segmentId = 'segment/id:with-special@chars';

        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString(rawurlencode('segment/id:with-special@chars'), $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 'segment/id:with-special@chars']));
        });

        $client->getSegmentDefinition($segmentId);
    }
}
