<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\Sites;

use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient as CoreHttpClient;
use Adobe\Client\Sites\ContentFragmentsClient;
use Adobe\Client\Tests\Http\MockPsr18Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ContentFragmentsClientTest extends TestCase
{
    private function makeClient(callable $handler): ContentFragmentsClient
    {
        $psr18 = new MockPsr18Client($handler);
        $factory = new HttpFactory();
        $core = new CoreHttpClient($psr18, $factory, $factory, new SdkConfig('https://example.test'));
        return new ContentFragmentsClient($core);
    }

    public function testList(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments?path=%2Fcontent%2Fdam%2Fsite', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['items' => []]));
        });

        $result = $client->list('/content/dam/site');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
    }

    public function testCreate(): void
    {
        $payload = ['path' => '/content/dam/site', 'model' => '/conf/site/models/article', 'name' => 'x'];
        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments', (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(201, ['Content-Type' => 'application/json'], json_encode(['path' => '/content/dam/site/x']));
        });

        $result = $client->create($payload);
        $this->assertSame('/content/dam/site/x', $result['path']);
    }

    public function testGet(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments/%2Fcontent%2Fdam%2Fsite%2Fx', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['name' => 'x']));
        });

        $result = $client->get('/content/dam/site/x');
        $this->assertSame('x', $result['name']);
    }

    public function testUpdate(): void
    {
        $payload = ['elements' => ['title' => ['value' => 'new']]];
        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('PATCH', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments/%2Fcontent%2Fdam%2Fsite%2Fx', (string) $request->getUri());
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['ok' => true]));
        });

        $result = $client->update('/content/dam/site/x', $payload);
        $this->assertTrue($result['ok']);
    }

    public function testDelete(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('DELETE', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments/%2Fcontent%2Fdam%2Fsite%2Fx', (string) $request->getUri());
            return new Response(204);
        });

        $client->delete('/content/dam/site/x');
        $this->assertTrue(true);
    }

    public function testDeleteAndUnpublish(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments/%2Fcontent%2Fdam%2Fsite%2Fx/delete-and-unpublish', (string) $request->getUri());
            return new Response(200);
        });

        $client->deleteAndUnpublish('/content/dam/site/x');
        $this->assertTrue(true);
    }

    public function testGetPreviewUrls(): void
    {
        $client = $this->makeClient(function ($request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments/%2Fcontent%2Fdam%2Fsite%2Fx/previews', (string) $request->getUri());
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['previews' => []]));
        });

        $result = $client->getPreviewUrls('/content/dam/site/x');
        $this->assertIsArray($result['previews']);
    }

    public function testCopy(): void
    {
        $payload = ['destinationPath' => '/content/dam/site/copies', 'name' => 'x-copy'];
        $client = $this->makeClient(function ($request) use ($payload) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertStringContainsString('/api/sites/v1/fragments/%2Fcontent%2Fdam%2Fsite%2Fx/copy', (string) $request->getUri());
            $this->assertSame(json_encode($payload), (string) $request->getBody());
            return new Response(201, ['Content-Type' => 'application/json'], json_encode(['path' => '/content/dam/site/copies/x-copy']));
        });

        $result = $client->copy('/content/dam/site/x', $payload);
        $this->assertSame('/content/dam/site/copies/x-copy', $result['path']);
    }
}


