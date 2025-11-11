# Adobe Client PHP

PSR-compliant PHP SDK for Adobe Experience Cloud services (AEM Sites, AEM Assets, AJO).

## Installation

```bash
composer require robert-ngo/adobe-client-php
```

The package includes Guzzle HTTP client and PSR-7/PSR-17 implementations by default. Everything you need is installed with one command!

**Using a different HTTP client?** The SDK is PSR-18 compliant and works with any PSR-18 HTTP client (Symfony HTTP Client, etc.). Simply install your preferred client and pass it to the `Sdk` constructor.

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

## DDEV (Optional)

Use DDEV for a reproducible PHP 8.2 environment with Composer and PHPUnit installed.

```bash
# Start DDEV (requires ddev installed locally)
ddev start

# Install dependencies
ddev composer install

# Run tests
ddev phpunit -q
```

## Architecture

- Core: PSR-18 HTTP client wrapper, config
- Auth: pluggable `AuthProvider` implementations (Bearer/OAuth/JWT)
- Sites, Assets: service-specific clients

Follows PSR-4 autoloading and PSR-12 coding style.

## Notes

API paths shown are placeholders and may require adjustment to your AEM deployment or Adobe APIs.
