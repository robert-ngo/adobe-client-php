# SegmentationClient Usage Guide

The `SegmentationClient` provides a comprehensive PHP interface for the Adobe Experience Platform Segmentation Service API. This client allows you to manage audiences, segment definitions, segment jobs, and export jobs programmatically.

## Table of Contents

- [Setup](#setup)
- [Configuration](#configuration)
- [Audiences](#audiences)
- [Export Jobs](#export-jobs)
- [Segment Definitions](#segment-definitions)
- [Segment Jobs](#segment-jobs)
- [Error Handling](#error-handling)

## Setup

### Installation

```bash
composer require adobe/adobe-client-php
```

### Basic Initialization

```php
<?php

use Adobe\Client\Auth\BearerTokenProvider;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient;
use Adobe\Client\Segmentation\SegmentationClient;
use GuzzleHttp\Psr7\HttpFactory;

// Configure with required Adobe Experience Platform headers
$headers = [
    'x-api-key' => 'your-api-key',
    'x-gw-ims-org-id' => 'your-ims-org-id',
    'x-sandbox-name' => 'prod', // or your sandbox name
];

$config = new SdkConfig('https://platform.adobe.io', 'my-app/1.0.0', $headers);
$auth = new BearerTokenProvider('your-access-token');

// Create HTTP client
$psr18Client = new \GuzzleHttp\Client();
$httpFactory = new HttpFactory();
$httpClient = new HttpClient($psr18Client, $httpFactory, $httpFactory, $config, $auth);

// Create Segmentation client
$segmentation = new SegmentationClient($httpClient);
```

## Configuration

The Segmentation Service API requires the following headers on all requests:

- `Authorization: Bearer {ACCESS_TOKEN}` - Provided by BearerTokenProvider
- `x-api-key: {API_KEY}` - Your API key (client ID)
- `x-gw-ims-org-id: {ORG_ID}` - Your IMS organization ID
- `x-sandbox-name: {SANDBOX_NAME}` - The sandbox name (e.g., "prod")

All of these are automatically added to requests through the SdkConfig and AuthProvider.

## Audiences

Audiences are collections of people, accounts, households, or other entities that share common characteristics.

### List Audiences

```php
// List all audiences with pagination
$audiences = $segmentation->listAudiences([
    'start' => 0,
    'limit' => 20,
]);

// List with filters
$audiences = $segmentation->listAudiences([
    'limit' => 50,
    'sort' => 'updateTime:desc',
    'name' => 'Customer',           // Filter by name (case-insensitive)
    'entityType' => 'segment',      // Filter by entity type
]);

foreach ($audiences['audiences'] as $audience) {
    echo "Audience: {$audience['name']} (ID: {$audience['id']})\n";
}
```

### Create an Audience

```php
$newAudience = $segmentation->createAudience([
    'name' => 'High-Value US Customers',
    'description' => 'Customers in the US with purchase history > $1000',
    'type' => 'SegmentDefinition',
    'expression' => [
        'type' => 'PQL',
        'format' => 'pql/text',
        'value' => 'homeAddress.country = "US" and totalPurchases > 1000',
    ],
    'schema' => [
        'name' => '_xdm.context.profile',
    ],
]);

echo "Created audience ID: {$newAudience['id']}\n";
```

### Get an Audience

```php
$audience = $segmentation->getAudience('60ccea95-1435-4180-97a5-58af4aa285ab');

echo "Name: {$audience['name']}\n";
echo "Description: {$audience['description']}\n";
echo "Status: {$audience['lifecycleState']}\n";
```

### Update an Audience (Full Replacement)

```php
$updatedAudience = $segmentation->updateAudience('audience-id', [
    'name' => 'Updated Audience Name',
    'description' => 'Updated description',
    'expression' => [
        'type' => 'PQL',
        'format' => 'pql/text',
        'value' => 'homeAddress.country = "CA"',
    ],
]);
```

### Patch an Audience (Partial Update)

```php
// Use JSON Patch operations
$patchedAudience = $segmentation->patchAudience('audience-id', [
    ['op' => 'replace', 'path' => '/name', 'value' => 'New Name'],
    ['op' => 'replace', 'path' => '/description', 'value' => 'New Description'],
]);
```

### Delete an Audience

```php
$segmentation->deleteAudience('audience-id');
echo "Audience deleted successfully\n";
```

### Bulk Get Audiences

```php
$audiences = $segmentation->bulkGetAudiences([
    '60ccea95-1435-4180-97a5-58af4aa285ab',
    '70ccea95-1435-4180-97a5-58af4aa285ac',
]);

foreach ($audiences['results'] as $audience) {
    echo "Audience: {$audience['name']}\n";
}
```

## Export Jobs

Export jobs are asynchronous processes used to persist audience members to datasets.

### List Export Jobs

```php
$exportJobs = $segmentation->listExportJobs([
    'limit' => 10,
    'offset' => 0,
    'status' => 'SUCCEEDED', // Optional: filter by status
]);

foreach ($exportJobs['records'] as $job) {
    echo "Export Job {$job['id']}: {$job['status']}\n";
}
```

### Create an Export Job

```php
$exportJob = $segmentation->createExportJob([
    'fields' => 'identities.id,personalEmail.address,person.name',
    'mergePolicy' => [
        'id' => 'your-merge-policy-id',
        'version' => 1,
    ],
    'filter' => [
        'segments' => [
            ['segmentId' => 'segment-id-1'],
            ['segmentId' => 'segment-id-2'],
        ],
    ],
    'destination' => [
        'datasetId' => 'your-dataset-id',
        'segmentPerBatch' => false,
    ],
]);

echo "Export job created with ID: {$exportJob['id']}\n";
echo "Status: {$exportJob['status']}\n";
```

### Get Export Job Status

```php
$exportJob = $segmentation->getExportJob('export-job-id');

echo "Status: {$exportJob['status']}\n";
echo "Progress: {$exportJob['metrics']['profileExportedCount']} profiles exported\n";
```

### Cancel an Export Job

```php
$result = $segmentation->cancelExportJob('export-job-id');
echo "Export job cancelled: {$result['message']}\n";
```

## Segment Definitions

Segment definitions include Profile Query Language (PQL) statements that define which profiles are part of an audience.

### List Segment Definitions

```php
$segments = $segmentation->listSegmentDefinitions([
    'start' => 0,
    'limit' => 25,
    'sort' => 'updateTime:desc',
]);

foreach ($segments['segments'] as $segment) {
    echo "Segment: {$segment['name']} (ID: {$segment['id']})\n";
}
```

### Create a Segment Definition

```php
$segment = $segmentation->createSegmentDefinition([
    'name' => 'Active Email Subscribers',
    'description' => 'Users who have opened an email in the last 30 days',
    'expression' => [
        'type' => 'PQL',
        'format' => 'pql/text',
        'value' => 'personalEmail.emailOpened = true and personalEmail.lastOpenedDate occurs < 30 days before now',
    ],
    'schema' => [
        'name' => '_xdm.context.profile',
    ],
    'ttlInDays' => 60,
]);

echo "Segment created: {$segment['id']}\n";
```

### Get a Segment Definition

```php
$segment = $segmentation->getSegmentDefinition('segment-id');

echo "Name: {$segment['name']}\n";
echo "PQL Expression: {$segment['expression']['value']}\n";
```

### Update a Segment Definition (Partial)

```php
$updatedSegment = $segmentation->patchSegmentDefinition('segment-id', [
    'name' => 'Updated Segment Name',
    'description' => 'Updated description',
]);
```

### Delete a Segment Definition

```php
$result = $segmentation->deleteSegmentDefinition('segment-id');
echo "Segment deleted: {$result['message']}\n";
```

### Bulk Get Segment Definitions

```php
$segments = $segmentation->bulkGetSegmentDefinitions([
    'segment-id-1',
    'segment-id-2',
    'segment-id-3',
]);

foreach ($segments['results'] as $segment) {
    echo "Segment: {$segment['name']}\n";
}
```

### Convert Segment Definition Between PQL Formats

```php
// Convert from pql/json to pql/text or vice versa
$converted = $segmentation->convertSegmentDefinition([
    'name' => 'Test Segment',
    'body' => [
        'xdmEntity' => [
            'workAddress' => [
                'country' => 'US',
            ],
        ],
    ],
]);

echo "Converted PQL: {$converted['expression']['value']}\n";
```

## Segment Jobs

Segment jobs process segment definitions to generate audiences.

### List Segment Jobs

```php
$jobs = $segmentation->listSegmentJobs([
    'start' => 0,
    'limit' => 20,
    'status' => 'SUCCEEDED',
    'sort' => 'updateTime:desc',
]);

foreach ($jobs['children'] as $job) {
    echo "Job {$job['id']}: {$job['status']}\n";
}
```

### Create a Segment Job

```php
$job = $segmentation->createSegmentJob([
    [
        'segmentId' => 'segment-id-1',
        'modelName' => '_xdm.context.profile',
    ],
    [
        'segmentId' => 'segment-id-2',
        'modelName' => '_xdm.context.profile',
    ],
]);

echo "Segment job created: {$job['id']}\n";
echo "Status: {$job['status']}\n";
```

### Get Segment Job Status

```php
$job = $segmentation->getSegmentJob('job-id');

echo "Status: {$job['status']}\n";
echo "Progress: {$job['metrics']['totalProfiles']} profiles processed\n";
echo "Qualified: {$job['metrics']['segmentedProfileCount']}\n";
```

### Cancel a Segment Job

```php
$segmentation->cancelSegmentJob('job-id');
echo "Segment job cancelled\n";
```

### Bulk Get Segment Jobs

```php
$jobs = $segmentation->bulkGetSegmentJobs([
    'job-id-1',
    'job-id-2',
]);

foreach ($jobs['results'] as $job) {
    echo "Job {$job['id']}: {$job['status']}\n";
}
```

## Error Handling

All methods throw `ApiException` when the API returns a non-2xx status code:

```php
use Adobe\Client\Exceptions\ApiException;

try {
    $audience = $segmentation->getAudience('non-existent-id');
} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";

    if ($response = $e->getResponse()) {
        echo "Status Code: " . $response->getStatusCode() . "\n";

        $body = json_decode((string) $response->getBody(), true);
        echo "Error Details: " . ($body['title'] ?? 'Unknown error') . "\n";
    }
}
```

## Common Query Parameters

### List Operations

Most list operations support these query parameters:

- `start` (int): Starting offset for pagination
- `limit` (int): Number of results per page
- `sort` (string): Sort field and direction, e.g., `"updateTime:desc"`
- `property` (string): Filter by exact property match, e.g., `"property=audienceId==test-id"`

### Status Values

Common status values across jobs:

- `NEW` - Job has been created
- `PROCESSING` - Job is currently running
- `SUCCEEDED` - Job completed successfully
- `FAILED` - Job failed
- `CANCELLED` - Job was cancelled

## Integration with Sdk.php

To integrate the SegmentationClient into the main Sdk class:

```php
// In src/Sdk.php

use Adobe\Client\Segmentation\SegmentationClient;

final class Sdk
{
    private SegmentationClient $segmentation;

    public function __construct(/* ... */)
    {
        // ... existing code ...
        $this->segmentation = new SegmentationClient($this->http);
    }

    public function segmentation(): SegmentationClient
    {
        return $this->segmentation;
    }
}
```

Then use it:

```php
$sdk = new Sdk(/* ... */);
$audiences = $sdk->segmentation()->listAudiences();
```

## Additional Resources

- [Adobe Experience Platform Segmentation Service Documentation](https://experienceleague.adobe.com/docs/experience-platform/segmentation/home.html)
- [Segmentation Service API Reference](https://experienceleague.adobe.com/docs/experience-platform/segmentation/api/overview.html)
- [Profile Query Language (PQL) Guide](https://experienceleague.adobe.com/docs/experience-platform/segmentation/pql/overview.html)
