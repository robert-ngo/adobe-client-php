# SegmentationClient Integration Guide

This document shows how to integrate the `SegmentationClient` into the main `Sdk` class.

## Step 1: Update `src/Sdk.php`

Add the following changes to `/Users/robertngo/Projects/adobe-client-php/src/Sdk.php`:

```php
<?php

declare(strict_types=1);

namespace Adobe\Client;

use Adobe\Client\Assets\AssetsClient;
use Adobe\Client\Core\AuthProvider;
use Adobe\Client\Core\HttpClient;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Segmentation\SegmentationClient;  // Add this import
use Adobe\Client\Sites\SitesClient;
use Adobe\Client\Sites\ContentFragmentsClient;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Sdk
{
    private HttpClient $http;
    private SitesClient $sites;
    private AssetsClient $assets;
    private ContentFragmentsClient $contentFragments;
    private SegmentationClient $segmentation;  // Add this property

    public function __construct(
        Psr18ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SdkConfig $config,
        ?AuthProvider $authProvider = null
    ) {
        $this->http = new HttpClient($httpClient, $requestFactory, $streamFactory, $config, $authProvider);
        $this->sites = new SitesClient($this->http);
        $this->assets = new AssetsClient($this->http);
        $this->contentFragments = new ContentFragmentsClient($this->http);
        $this->segmentation = new SegmentationClient($this->http);  // Add this line
    }

    public function sites(): SitesClient
    {
        return $this->sites;
    }

    public function assets(): AssetsClient
    {
        return $this->assets;
    }

    public function contentFragments(): ContentFragmentsClient
    {
        return $this->contentFragments;
    }

    public function segmentation(): SegmentationClient  // Add this method
    {
        return $this->segmentation;
    }
}
```

## Step 2: Usage Example

After integrating the client into `Sdk.php`, you can use it as follows:

```php
<?php

use Adobe\Client\Auth\BearerTokenProvider;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Sdk;
use GuzzleHttp\Psr7\HttpFactory;

// Configure with Adobe Experience Platform headers
$headers = [
    'x-api-key' => 'your-api-key',
    'x-gw-ims-org-id' => 'your-ims-org-id',
    'x-sandbox-name' => 'prod',
];

$config = new SdkConfig('https://platform.adobe.io', 'my-app/1.0.0', $headers);
$auth = new BearerTokenProvider('your-access-token');

// Create SDK instance
$psr18Client = new \GuzzleHttp\Client();
$httpFactory = new HttpFactory();
$sdk = new Sdk($psr18Client, $httpFactory, $httpFactory, $config, $auth);

// Use the Segmentation client
$audiences = $sdk->segmentation()->listAudiences(['limit' => 10]);

foreach ($audiences['audiences'] as $audience) {
    echo "Audience: {$audience['name']} (ID: {$audience['id']})\n";
}
```

## Direct Usage (Without Sdk.php)

You can also use the `SegmentationClient` directly without integrating it into `Sdk.php`:

```php
<?php

use Adobe\Client\Auth\BearerTokenProvider;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Core\HttpClient;
use Adobe\Client\Segmentation\SegmentationClient;
use GuzzleHttp\Psr7\HttpFactory;

// Configure
$headers = [
    'x-api-key' => 'your-api-key',
    'x-gw-ims-org-id' => 'your-ims-org-id',
    'x-sandbox-name' => 'prod',
];

$config = new SdkConfig('https://platform.adobe.io', 'my-app/1.0.0', $headers);
$auth = new BearerTokenProvider('your-access-token');

// Create HTTP client
$psr18Client = new \GuzzleHttp\Client();
$httpFactory = new HttpFactory();
$httpClient = new HttpClient($psr18Client, $httpFactory, $httpFactory, $config, $auth);

// Create Segmentation client directly
$segmentation = new SegmentationClient($httpClient);

// Use the client
$audiences = $segmentation->listAudiences(['limit' => 10]);
```

## Required Configuration

The Adobe Experience Platform Segmentation Service requires these headers on all requests:

1. **Authorization**: `Bearer {ACCESS_TOKEN}`
   - Automatically added by `BearerTokenProvider`

2. **x-api-key**: Your API key (client ID)
   - Add to `$headers` array in `SdkConfig`

3. **x-gw-ims-org-id**: Your IMS organization ID
   - Add to `$headers` array in `SdkConfig`

4. **x-sandbox-name**: Sandbox name (e.g., "prod")
   - Add to `$headers` array in `SdkConfig`

## Environment Variables Setup

For the example file (`examples/segmentation-example.php`), set these environment variables:

```bash
export AEP_BASE_URI="https://platform.adobe.io"
export AEP_ACCESS_TOKEN="your-access-token"
export AEP_API_KEY="your-api-key"
export AEP_IMS_ORG_ID="your-ims-org-id@AdobeOrg"
export AEP_SANDBOX_NAME="prod"
```

Then run:

```bash
php examples/segmentation-example.php
```

## Testing

Run the SegmentationClient tests:

```bash
# Using DDEV
ddev phpunit tests/Segmentation/SegmentationClientTest.php

# Or directly with PHPUnit
vendor/bin/phpunit tests/Segmentation/SegmentationClientTest.php

# With detailed test descriptions
vendor/bin/phpunit tests/Segmentation/SegmentationClientTest.php --testdox
```

All 31 tests should pass with 121 assertions.

## File Structure

The SegmentationClient follows the established architecture:

```
src/
└── Segmentation/
    └── SegmentationClient.php

tests/
└── Segmentation/
    └── SegmentationClientTest.php

examples/
└── segmentation-example.php

docs/
├── segmentation.yaml
├── SEGMENTATION_CLIENT_USAGE.md
└── SEGMENTATION_CLIENT_INTEGRATION.md
```

## API Endpoints Implemented

### Audiences
- `listAudiences()` - GET /audiences
- `createAudience()` - POST /audiences
- `getAudience()` - GET /audiences/{AUDIENCE_ID}
- `deleteAudience()` - DELETE /audiences/{AUDIENCE_ID}
- `patchAudience()` - PATCH /audiences/{AUDIENCE_ID}
- `updateAudience()` - PUT /audiences/{AUDIENCE_ID}
- `bulkGetAudiences()` - POST /audiences/bulk-get

### Export Jobs
- `listExportJobs()` - GET /export/jobs
- `createExportJob()` - POST /export/jobs
- `getExportJob()` - GET /export/jobs/{EXPORT_JOB_ID}
- `cancelExportJob()` - DELETE /export/jobs/{EXPORT_JOB_ID}

### Segment Definitions
- `listSegmentDefinitions()` - GET /segment/definitions
- `createSegmentDefinition()` - POST /segment/definitions
- `getSegmentDefinition()` - GET /segment/definitions/{SEGMENT_ID}
- `deleteSegmentDefinition()` - DELETE /segment/definitions/{SEGMENT_ID}
- `patchSegmentDefinition()` - PATCH /segment/definitions/{SEGMENT_ID}
- `bulkGetSegmentDefinitions()` - POST /segment/definitions/bulk-get
- `convertSegmentDefinition()` - POST /segment/conversion

### Segment Jobs
- `listSegmentJobs()` - GET /segment/jobs
- `createSegmentJob()` - POST /segment/jobs
- `getSegmentJob()` - GET /segment/jobs/{SEGMENT_JOB_ID}
- `cancelSegmentJob()` - DELETE /segment/jobs/{SEGMENT_JOB_ID}
- `bulkGetSegmentJobs()` - POST /segment/jobs/bulk-get

## Additional Resources

- See `docs/SEGMENTATION_CLIENT_USAGE.md` for detailed usage examples
- See `docs/segmentation.yaml` for the complete OpenAPI specification
- See `examples/segmentation-example.php` for a working example
