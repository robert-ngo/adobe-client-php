<?php

declare(strict_types=1);

use Adobe\Client\Auth\BearerTokenProvider;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Sdk;

// Autoload dependencies and SDK
require dirname(__DIR__) . '/vendor/autoload.php';

// Simple helper to detect and create PSR-18 client and PSR-17 factories
// Tries, in order: HTTPlug Discovery, Guzzle factories + Guzzle client
// Returns [Psr18Client, RequestFactory, StreamFactory]
/**
 * @return array{0:Psr\Http\Client\ClientInterface,1:Psr\Http\Message\RequestFactoryInterface,2:Psr\Http\Message\StreamFactoryInterface}
 */
function discoverHttpStack(): array
{
    // 1) HTTPlug discovery if available
    if (class_exists('Http\Discovery\Psr18ClientDiscovery') &&
        class_exists('Http\Discovery\Psr17FactoryDiscovery')) {
        /** @var Psr\Http\Client\ClientInterface $client */
        $client = Http\Discovery\Psr18ClientDiscovery::find();
        /** @var Psr\Http\Message\RequestFactoryInterface $requestFactory */
        $requestFactory = Http\Discovery\Psr17FactoryDiscovery::findRequestFactory();
        /** @var Psr\Http\Message\StreamFactoryInterface $streamFactory */
        $streamFactory = Http\Discovery\Psr17FactoryDiscovery::findStreamFactory();
        return [$client, $requestFactory, $streamFactory];
    }

    // 2) Guzzle PSR-17 factories + Guzzle client if available
    if (class_exists('Http\Factory\Guzzle\RequestFactory') &&
        class_exists('Http\Factory\Guzzle\StreamFactory') &&
        class_exists('GuzzleHttp\\Client')) {
        /** @var Psr\Http\Message\RequestFactoryInterface $requestFactory */
        $requestFactory = new Http\Factory\Guzzle\RequestFactory();
        /** @var Psr\Http\Message\StreamFactoryInterface $streamFactory */
        $streamFactory = new Http\Factory\Guzzle\StreamFactory();
        /** @var Psr\Http\Client\ClientInterface $client */
        $client = new GuzzleHttp\Client();
        return [$client, $requestFactory, $streamFactory];
    }

    throw new RuntimeException(
        'No PSR-18 client and PSR-17 factories detected. ' .
        'Install one of: "php-http/discovery" with a compatible client (e.g. "guzzlehttp/guzzle"), or ' .
        'install "http-interop/http-factory-guzzle" and "guzzlehttp/guzzle".'
    );
}

// Read inputs from GET or environment variables
$baseUri = (string) ($_GET['base_uri'] ?? getenv('AEM_BASE_URI') ?: '');
$token = (string) ($_GET['access_token'] ?? getenv('AEM_ACCESS_TOKEN') ?: '');
$containerPath = (string) ($_GET['path'] ?? '/content/dam');
$limit = is_numeric($_GET['limit'] ?? '') ? (int) $_GET['limit'] : 25;
$offset = is_numeric($_GET['offset'] ?? '') ? (int) $_GET['offset'] : 0;
$model = (string) ($_GET['model'] ?? '');
$search = (string) ($_GET['search'] ?? '');
$sort = (string) ($_GET['sort'] ?? '');
$recursive = isset($_GET['recursive']) ? (bool) $_GET['recursive'] : false;

$error = null;
$result = null;

if ($baseUri !== '' && $token !== '') {
    try {
        [$psr18, $requestFactory, $streamFactory] = discoverHttpStack();

        $headers = [];
        $config = new SdkConfig($baseUri, 'adobe-client-php-example/0.1.0', $headers);
        $auth = new BearerTokenProvider($token);
        $sdk = new Sdk($psr18, $requestFactory, $streamFactory, $config, $auth);

        $options = [
            'limit' => $limit,
            'offset' => $offset,
        ];
        if ($model !== '') {
            $options['model'] = $model;
        }
        if ($search !== '') {
            $options['search'] = $search;
        }
        if ($sort !== '') {
            $options['sort'] = $sort;
        }
        if ($recursive) {
            $options['recursive'] = 1;
        }

        $result = $sdk->contentFragments()->list($containerPath, $options);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Adobe Client PHP — List Content Fragments</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; margin: 2rem; }
        form { display: grid; grid-template-columns: 180px 1fr; gap: .5rem 1rem; max-width: 960px; }
        label { align-self: center; }
        input[type="text"], input[type="password"], input[type="url"], input[type="number"] { width: 100%; padding: .5rem; }
        .row { grid-column: 1 / -1; }
        .btn { padding: .6rem 1rem; font-weight: 600; }
        pre { background: #f6f8fa; padding: 1rem; overflow: auto; border-radius: 6px; }
        .error { color: #b00020; font-weight: 600; }
        fieldset { grid-column: 1 / -1; }
        legend { font-weight: 700; }
    </style>
    <!--
      Usage:
      - Place this file under a web root and open it in a browser, OR run: php -S 127.0.0.1:8000 -t examples
      - Provide Base URI and Access Token via the form, or via env vars AEM_BASE_URI and AEM_ACCESS_TOKEN
    -->
    </head>
<body>
    <h1>List Content Fragments</h1>
    <form method="get">
        <label for="base_uri">AEM Base URI</label>
        <input id="base_uri" name="base_uri" type="url" placeholder="https://author.example.com" value="<?= htmlspecialchars($baseUri) ?>" required />

        <label for="access_token">Access Token</label>
        <input id="access_token" name="access_token" type="password" placeholder="Bearer token" value="<?= htmlspecialchars($token) ?>" required />

        <label for="path">Container Path</label>
        <input id="path" name="path" type="text" value="<?= htmlspecialchars($containerPath) ?>" />

        <label for="limit">Limit</label>
        <input id="limit" name="limit" type="number" min="1" max="200" value="<?= (int) $limit ?>" />

        <label for="offset">Offset</label>
        <input id="offset" name="offset" type="number" min="0" value="<?= (int) $offset ?>" />

        <label for="model">Model (optional)</label>
        <input id="model" name="model" type="text" value="<?= htmlspecialchars($model) ?>" />

        <label for="search">Search (optional)</label>
        <input id="search" name="search" type="text" value="<?= htmlspecialchars($search) ?>" />

        <label for="sort">Sort (optional)</label>
        <input id="sort" name="sort" type="text" placeholder="e.g. name asc" value="<?= htmlspecialchars($sort) ?>" />

        <label for="recursive">Recursive</label>
        <input id="recursive" name="recursive" type="checkbox" value="1" <?= $recursive ? 'checked' : '' ?> />

        <div class="row">
            <button class="btn" type="submit">Fetch</button>
        </div>
    </form>

    <?php if ($error !== null): ?>
        <p class="error">Error: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (is_array($result)): ?>
        <h2>Response</h2>
        <pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>

        <?php if (isset($result['items']) && is_array($result['items'])): ?>
            <h2>Items</h2>
            <ul>
                <?php foreach ($result['items'] as $item): ?>
                    <li>
                        <?= htmlspecialchars($item['title'] ?? ($item['name'] ?? '[untitled]')) ?>
                        <small style="color:#555">(<?= htmlspecialchars($item['path'] ?? '') ?>)</small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>


