# Adobe Client PHP

PSR-compliant PHP SDK for Adobe Experience Cloud services (AEM Sites, AEM Assets, AJO).

## Installation

```bash
composer require robertngo/adobe-client-php
```

You must provide PSR-18 HTTP client and PSR-17 factories. For example, with Guzzle:

```bash
composer require guzzlehttp/guzzle guzzlehttp/psr7 http-interop/http-factory-guzzle
```

## Quick Start

```php
<?php

use Adobe\Client\Sdk;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Auth\BearerTokenProvider;
use GuzzleHttp\Client as GuzzleClient;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;

$http = new GuzzleClient();
$requestFactory = new RequestFactory();
$streamFactory = new StreamFactory();
$config = new SdkConfig('https://your-aem-host');
$auth = new BearerTokenProvider('ACCESS_TOKEN');

$sdk = new Sdk($http, $requestFactory, $streamFactory, $config, $auth);

$pages = $sdk->sites()->listPages('/content/we-retail');
$asset = $sdk->assets()->get('/content/dam/path/to/asset.jpg');
```

## Architecture

- Core: PSR-18 HTTP client wrapper, config
- Auth: pluggable `AuthProvider` implementations (Bearer/OAuth/JWT)
- Sites, Assets: service-specific clients

Follows PSR-4 autoloading and PSR-12 coding style.

## Notes

API paths shown are placeholders and may require adjustment to your AEM deployment or Adobe APIs.
