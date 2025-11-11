<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\Segmentation;

use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient as CoreHttpClient;
use Adobe\Client\Exceptions\ApiException;
use Adobe\Client\Segmentation\ExportJobsClient;
use Adobe\Client\Tests\Http\MockPsr18Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ExportJobsClientTest extends TestCase
{
    private function makeClient(callable $handler): ExportJobsClient
    {
        $psr18 = new MockPsr18Client($handler);
        $factory = new HttpFactory();
        $core = new CoreHttpClient($psr18, $factory, $factory, new SdkConfig('https://platform.adobe.io'));
        return new ExportJobsClient($core);
    }

    public function testListExportJobs(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/export/jobs', (string) $request->getUri());
            $this->assertStringContainsString('limit=10', (string) $request->getUri());
            $this->assertStringContainsString('offset=0', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'records' => [],
                'page' => ['totalCount' => 0],
            ]));
        });

        $result = $client->listExportJobs(['limit' => 10, 'offset' => 0]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('records', $result);
    }

    public function testListExportJobsWithStatusFilter(): void
    {
        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString('status=SUCCEEDED', $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['records' => []]));
        });

        $result = $client->listExportJobs(['status' => 'SUCCEEDED']);
        $this->assertIsArray($result);
    }

    public function testCreateExportJob(): void
    {
        $payload = [
            'fields' => 'identities.id,personalEmail.address',
            'mergePolicy' => [
                'id' => 'merge-policy-id',
                'version' => 1,
            ],
            'filter' => [
                'segments' => [
                    ['segmentId' => 'segment-id-1'],
                ],
            ],
            'destination' => [
                'datasetId' => 'dataset-id',
                'segmentPerBatch' => false,
            ],
        ];

        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/export/jobs', (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'export-job-id',
                'status' => 'NEW',
            ]));
        });

        $result = $client->createExportJob($payload);
        $this->assertSame('export-job-id', $result['id']);
        $this->assertSame('NEW', $result['status']);
    }

    public function testGetExportJob(): void
    {
        $exportJobId = 'export-job-id-123';

        $client = $this->makeClient(function ($request) use ($exportJobId) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/export/jobs/' . $exportJobId, (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $exportJobId,
                'status' => 'SUCCEEDED',
            ]));
        });

        $result = $client->getExportJob($exportJobId);
        $this->assertSame($exportJobId, $result['id']);
        $this->assertSame('SUCCEEDED', $result['status']);
    }

    public function testCancelExportJob(): void
    {
        $exportJobId = 'export-job-id-123';

        $client = $this->makeClient(function ($request) use ($exportJobId) {
            $this->assertSame('DELETE', $request->getMethod());
            $this->assertStringContainsString('/data/core/ups/export/jobs/' . $exportJobId, (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'status' => true,
                'message' => 'Export job cancelled',
            ]));
        });

        $result = $client->cancelExportJob($exportJobId);
        $this->assertTrue($result['status']);
    }

    public function testListExportJobsThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to list export jobs');

        $client = $this->makeClient(function ($request) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'status' => 500,
                'title' => 'Internal server error',
            ]));
        });

        $client->listExportJobs();
    }

    public function testCreateExportJobThrowsOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to create export job');

        $client = $this->makeClient(function ($request) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'status' => 400,
                'title' => 'Bad request',
            ]));
        });

        $client->createExportJob(['fields' => 'test']);
    }

    public function testGetExportJobThrowsOnNotFound(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to retrieve export job');

        $client = $this->makeClient(function ($request) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'status' => 404,
                'title' => 'Export job not found',
            ]));
        });

        $client->getExportJob('non-existent-id');
    }

    public function testExportJobIdIsUrlEncoded(): void
    {
        $exportJobId = 'job/id:with-special@chars';

        $client = $this->makeClient(function ($request) {
            $uri = (string) $request->getUri();
            $this->assertStringContainsString(rawurlencode('job/id:with-special@chars'), $uri);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 'job/id:with-special@chars']));
        });

        $client->getExportJob($exportJobId);
    }
}
