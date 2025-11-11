# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PSR-compliant PHP SDK for Adobe Experience Cloud services (AEM Sites, AEM Assets, Adobe Journey Optimizer). The SDK uses dependency injection and follows SOLID principles with strict PSR compliance.

## Architecture

The `Sdk` class (`src/Sdk.php`) is the main entry point:
- User code → `Sdk` → `Core\HttpClient` (applies base URI, headers, auth) → service clients (Sites, Assets, ContentFragments) → Adobe APIs

**Key Components:**
- `src/Sdk.php` — SDK facade, instantiates and wires service clients
- `src/Core/HttpClient.php` — Central request builder; adds `Accept: application/json`, `User-Agent`, auth, and default headers from `SdkConfig`
- `src/Core/SdkConfig.php` — Configuration for base URI, user agent, and default headers
- `src/Core/AuthProvider.php` — Interface for authentication strategies
- `src/Auth/BearerTokenProvider.php` — Concrete auth implementation (Bearer token)
- `src/Sites/ContentFragmentsClient.php` — Reference implementation showing query building, JSON requests, error handling
- `src/Assets/AssetsClient.php` — Example for PUT operations with `StreamInterface` and custom headers

**Request Flow:**
1. Service client creates request via `HttpClient::createRequest()` or `createJsonRequest()`
2. HttpClient applies base URI, default headers, and calls `AuthProvider::authenticate()` if present
3. PSR-18 client sends request
4. Service client checks status code and throws `ApiException` on non-2xx responses

## Development Commands

**With DDEV (recommended for reproducible PHP 8.2 environment):**
```bash
ddev start                    # Start DDEV environment
ddev composer install         # Install dependencies
ddev phpunit -q              # Run all tests
ddev phpunit --filter TestName  # Run specific test
ddev composer update          # Update dependencies
```

**Without DDEV:**
```bash
composer install              # Install dependencies
vendor/bin/phpunit           # Run all tests
vendor/bin/phpunit --filter TestName  # Run specific test
vendor/bin/psalm             # Run static analysis (if configured)
php -S 127.0.0.1:8000 -t examples  # Run example web UI
```

## Code Patterns & Conventions

**Strict Typing:**
- All files use `declare(strict_types=1);` at the top
- All properties and methods have explicit type declarations
- Preserve this pattern in all new code

**PSR Standards:**
- PSR-4 autoloading: `Adobe\Client\` namespace maps to `src/`
- PSR-7 HTTP messages
- PSR-18 HTTP client abstraction
- PSR-12 coding style
- PSR-17 HTTP factories

**Request Creation:**
- Always use `HttpClient::createRequest()` or `createJsonRequest()`
- Never manually construct PSR-7 requests
- HttpClient automatically applies base URI, headers, and authentication

**Error Handling:**
- Service clients check `if ($response->getStatusCode() >= 300)`
- Throw `ApiException` for non-2xx responses
- Keep this pattern consistent for caller expectations

**Path Encoding:**
- Use `rawurlencode()` for repository paths (see `ContentFragmentsClient::encodePath()`)
- Keep slashes percent-encoded to treat paths as single segments
- Example: `/content/dam/site/x` → `%2Fcontent%2Fdam%2Fsite%2Fx`

**Headers:**
- `SdkConfig::getDefaultHeaders()` applied in `HttpClient::createRequest()`
- Don't duplicate default header logic in service clients

## Testing Patterns

Tests use `MockPsr18Client` (see `tests/Http/MockPsr18Client.php`) that accepts a callable to assert on outgoing requests.

**Example patterns from `tests/Sites/ContentFragmentsClientTest.php`:**

```php
// Assert HTTP method
$this->assertSame('GET', $request->getMethod());

// Assert URI contains encoded path
$this->assertStringContainsString('%2Fcontent%2Fdam%2Fsite', (string) $request->getUri());

// Assert JSON body
$expectedJson = json_encode($payload);
$this->assertSame($expectedJson, (string) $request->getBody());

// Assert headers
$this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
```

## Adding New Service Clients

1. Create new client class in appropriate namespace (e.g., `src/Journey/JourneyClient.php`)
2. Inject `HttpClient` via constructor
3. Use `$this->client->createRequest()` or `createJsonRequest()` for all requests
4. Check response status and throw `ApiException` on errors
5. Register client in `src/Sdk.php` constructor and add getter method
6. Add corresponding tests using `MockPsr18Client`

## Adding New Auth Providers

1. Create class implementing `AuthProvider` interface in `src/Auth/`
2. Implement `authenticate(RequestInterface): RequestInterface` method
3. `HttpClient` will automatically call this if provided to `Sdk` constructor
4. Add tests asserting correct headers/modifications to request

## Notes

- Adobe API endpoint paths in this SDK may be placeholders; adjust based on your AEM deployment
- Always preserve public APIs in `src/` unless planning a breaking release
- Keep focused, small changes in PRs
- Run `vendor/bin/phpunit` before committing
