# Content Fragments Client – Technical Examples

This document provides concrete, end-to-end examples for using `Sites\ContentFragmentsClient` from this SDK to manage Adobe Experience Manager (AEM) Content Fragments.

## Prerequisites

- AEM instance base URL (author or publish) and an access token with required permissions.
- PSR-18 HTTP client and PSR-17 factories installed (e.g., Guzzle stack):

```bash
composer require guzzlehttp/guzzle guzzlehttp/psr7 http-interop/http-factory-guzzle
```

## SDK Initialization

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
$cf = $sdk->contentFragments();
```

## List All Content Fragments

```php
$list = $cf->list('/content/dam/my-site', [
  'limit' => 25,
  'offset' => 0,
  // 'model' => '/conf/my-site/settings/dam/cfm/models/article',
  // 'recursive' => true,
  // 'search' => 'welcome',
  // 'sort' => 'name:asc',
]);

foreach (($list['items'] ?? []) as $item) {
  // $item contains fragment metadata (name, path, model, etc.)
}
```

## Create a Content Fragment

```php
$newFragment = $cf->create([
  'path' => '/content/dam/my-site',
  'model' => '/conf/my-site/settings/dam/cfm/models/article',
  'name' => 'my-article-fragment',
  'title' => 'My Article',
  'elements' => [
    'title' => ['value' => 'Hello'],
    'body' => ['value' => 'World'],
  ],
]);
```

## Get a Content Fragment

```php
$fragment = $cf->get('/content/dam/my-site/my-article-fragment');
```

## Edit (Update) a Content Fragment

```php
$updated = $cf->update('/content/dam/my-site/my-article-fragment', [
  'elements' => [
    'title' => ['value' => 'Updated Title'],
  ],
]);
```

## Delete a Content Fragment

```php
$cf->delete('/content/dam/my-site/my-article-fragment');
```

## Delete and Unpublish a Content Fragment

```php
$cf->deleteAndUnpublish('/content/dam/my-site/my-article-fragment');
```

## Get Preview URLs for a Content Fragment

```php
$previews = $cf->getPreviewUrls('/content/dam/my-site/my-article-fragment');
// $previews['previews'] may contain URLs to preview environments
```

## Copy a Content Fragment

```php
$copy = $cf->copy('/content/dam/my-site/my-article-fragment', [
  'destinationPath' => '/content/dam/my-site/copies',
  'name' => 'my-article-fragment-copy',
  // 'title' => 'My Copied Article',
]);
```

## Error Handling

`ContentFragmentsClient` throws `Adobe\Client\Exceptions\ApiException` for non-2xx responses. You can catch and inspect the response:

```php
use Adobe\Client\Exceptions\ApiException;

try {
  $cf->delete('/content/dam/my-site/does-not-exist');
} catch (ApiException $e) {
  $response = $e->getResponse();
  $status = $response ? $response->getStatusCode() : 0;
}
```

## Notes and Tips

- Paths: pass full repository paths, e.g. `/content/dam/...`.
- Models: use the correct model path for your project (e.g. `/conf/<site>/settings/dam/cfm/models/<model>`).
- Encoding: the client URL-encodes the fragment path in requests.
- Headers: set default headers globally via `SdkConfig` if needed (e.g., `x-gw-ims-org-id`).

## Reference

- AEM Sites API – Fragment Management documentation:
  - `https://developer.adobe.com/experience-cloud/experience-manager-apis/api/stable/sites/#tag/Fragment-Management`
