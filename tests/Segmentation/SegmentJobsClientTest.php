<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\Segmentation;

use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient as CoreHttpClient;
use Adobe\Client\Exceptions\ApiException;
use Adobe\Client\Segmentation\SegmentJobsClient;
use Adobe\Client\Tests\Http\MockPsr18Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SegmentJobsClientTest extends TestCase
{
    private function makeClient(callable $handler): SegmentJobsClient
    {
        $psr18 = new MockPsr18Client($handler);
        $factory = new HttpFactory();
        $core = new CoreHttpClient($psr18, $factory, $factory, new SdkConfig('https://platform.adobe.io'));
        return new SegmentJobsClient($core);
    }

    public function testListSegmentJobs(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/jobs', (string) $request->getUri());
            $this->assertStringContainsString('start=0', (string) $request->getUri());
            $this->assertStringContainsString('limit=25', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'children' => [],
                'page' => ['totalCount' => 0],
            ]));
        });

        $result = $client->listSegmentJobs(['start' => 0, 'limit' => 25]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('children', $result);
    }

    public function testListSegmentJobsWithFilters(): void
    {
        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString('status=SUCCEEDED', $uri);
            $this->assertStringContainsString('sort=createdTime%3Adesc', $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['children' => []]));
        });

        $result = $client->listSegmentJobs([
            'status' => 'SUCCEEDED',
            'sort' => 'createdTime:desc',
        ]);
        $this->assertIsArray($result);
    }

    public function testCreateSegmentJob(): void
    {
        $segmentJobRequests = [
            [
                'segmentId' => 'segment-id-1',
                'modelName' => 'profile',
            ],
        ];

        $client = $this->makeClient(function ($request) use ($segmentJobRequests) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/jobs', (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            $this->assertSame(json_encode($segmentJobRequests), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'job-id',
                'status' => 'NEW',
            ]));
        });

        $result = $client->createSegmentJob($segmentJobRequests);
        $this->assertSame('job-id', $result['id']);
        $this->assertSame('NEW', $result['status']);
    }

    public function testGetSegmentJob(): void
    {
        $segmentJobId = 'job-id-123';

        $client = $this->makeClient(function ($request) use ($segmentJobId) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/jobs/' . $segmentJobId, (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $segmentJobId,
                'status' => 'PROCESSING',
            ]));
        });

        $result = $client->getSegmentJob($segmentJobId);
        $this->assertSame($segmentJobId, $result['id']);
        $this->assertSame('PROCESSING', $result['status']);
    }

    public function testCancelSegmentJob(): void
    {
        $segmentJobId = 'job-id-123';

        $client = $this->makeClient(function ($request) use ($segmentJobId) {
            $this->assertSame('DELETE', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/jobs/' . $segmentJobId, (string) $request->getUri());
            return new Response(204);
        });

        $client->cancelSegmentJob($segmentJobId);
        $this->assertTrue(true);
    }

    public function testBulkGetSegmentJobs(): void
    {
        $ids = ['job-id-1', 'job-id-2'];

        $client = $this->makeClient(function ($request) use ($ids) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/segment/jobs/bulk-get', (string) $request->getUri());
            $this->assertSame(json_encode(['ids' => $ids]), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'results' => [
                    ['id' => $ids[0], 'status' => 'SUCCEEDED'],
                    ['id' => $ids[1], 'status' => 'PROCESSING'],
                ],
            ]));
        });

        $result = $client->bulkGetSegmentJobs($ids);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
    }

    public function testListSegmentJobsThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to list segment jobs');

        $client = $this->makeClient(function ($request) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'status' => 500,
                'title' => 'Internal server error',
            ]));
        });

        $client->listSegmentJobs();
    }

    public function testCreateSegmentJobThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to create segment job');

        $client = $this->makeClient(function ($request) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'status' => 400,
                'title' => 'Bad request',
            ]));
        });

        $client->createSegmentJob([['segmentId' => 'test']]);
    }

    public function testGetSegmentJobThrowsOnNotFound(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to retrieve segment job');

        $client = $this->makeClient(function ($request) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'status' => 404,
                'title' => 'Job not found',
            ]));
        });

        $client->getSegmentJob('non-existent-id');
    }

    public function testCancelSegmentJobThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to cancel segment job');

        $client = $this->makeClient(function ($request) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'status' => 400,
                'title' => 'Cannot cancel job',
            ]));
        });

        $client->cancelSegmentJob('job-id');
    }

    public function testSegmentJobIdIsUrlEncoded(): void
    {
        $segmentJobId = 'job/id:with-special@chars';

        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString(rawurlencode('job/id:with-special@chars'), $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 'job/id:with-special@chars']));
        });

        $client->getSegmentJob($segmentJobId);
    }
}
