## Purpose

Short, actionable guidance for AI coding agents working in this repository. Focus on the concrete patterns, files, and commands an agent should use or follow when changing the SDK.

## Quick context (big picture)

- This is a PSR-first PHP SDK for Adobe Experience Cloud (AEM Sites, AEM Assets, etc.). The `Sdk` class (see `src/Sdk.php`) is the facade that instantiates a thin `HttpClient` and service clients (Sites, Assets, ContentFragments).
- Flow: User code -> `Sdk` -> `Core\HttpClient` (applies base URI, headers, auth) -> service clients (e.g. `src/Sites/ContentFragmentsClient.php`, `src/Assets/AssetsClient.php`) -> external AEM endpoints.

## Key files to inspect when modifying behavior

- `src/Sdk.php` — SDK entry point and wiring for clients. Prefer changing wiring here for new clients.
- `src/Core/HttpClient.php` — central request builder. Adds `Accept: application/json`, `User-Agent`, default headers from `SdkConfig`, and calls `AuthProvider::authenticate(Request)` when present.
- `src/Core/SdkConfig.php` — base URI, user agent and default headers. `SdkConfig` rtrim/sanitizes base URI.
- `src/Auth/` — auth providers (e.g. `BearerTokenProvider.php`). Implement `AuthProvider` to add authentication.
- `src/Sites/ContentFragmentsClient.php` — concrete patterns for building queries, JSON requests, and error handling (throws `ApiException` on >=300).
- `src/Assets/AssetsClient.php` — example for PUT with `StreamInterface` and custom metadata headers.
- `examples/web-list-content-fragments.php` — runnable example showing PSR-18/17 discovery and example usage of `Sdk::contentFragments()->list()`.
- `tests/` and `tests/Http/MockPsr18Client.php` — unit-test patterns; tests assert request method, URI, headers and body; use `GuzzleHttp\Psr7\HttpFactory` in tests.

## Concrete patterns & conventions (follow exactly)

- Strict typing: all files use `declare(strict_types=1);` and typed properties/returns. Preserve this.
- PSR-4 autoloading: namespace `Adobe\Client\` maps to `src/` (see `composer.json`).
- Request creation: always use `HttpClient::createRequest()` or `createJsonRequest()` instead of manually constructing PSR-7 messages across the repo.
- Error handling: service clients check `if ($response->getStatusCode() >= 300)` and throw `ApiException`. Keep this pattern so tests and callers expect exceptions for non-2xx responses.
- Path encoding: `ContentFragmentsClient::encodePath()` percent-encodes repository paths (uses `rawurlencode`) — follow this when building URIs for resources with slashes.
- Headers: `SdkConfig::getDefaultHeaders()` is applied in `HttpClient::createRequest()`; don't duplicate default header logic elsewhere.

## Tests & local dev commands (what to run)

- Recommended (reproducible): DDEV (see `README.md` / `CLAUDE.md`):
  - ddev start
  - ddev composer install
  - ddev phpunit -q
- Local without DDEV:
  - composer install
  - vendor/bin/phpunit
  - Run an example web UI: from repo root `php -S 127.0.0.1:8000 -t examples` then open `examples/web-list-content-fragments.php` in a browser.

## Test patterns to copy (examples)

- Unit tests use a mock PSR-18 client that accepts a callable and asserts on the incoming `RequestInterface`. See `tests/Sites/ContentFragmentsClientTest.php` for examples:
  - assert method: `$this->assertSame('GET', $request->getMethod());`
  - assert URI contains encoded path: `assertStringContainsString('%2Fcontent%2Fdam%2Fsite', (string) $request->getUri());`
  - assert JSON body: compare `json_encode($payload)` to `(string)$request->getBody()`.

## When to add / change an AuthProvider

- Add new auth strategies under `src/Auth/` implementing the `AuthProvider` contract. `HttpClient` will call `authenticate(RequestInterface): RequestInterface` if an `AuthProvider` is provided to the `Sdk` constructor.

## Pull request tips for AI agents

- Small, focused changes. Preserve public APIs in `src/` unless a breaking release is intended.
- Update or add unit tests under `tests/` that assert on the outgoing request (use `MockPsr18Client`). Tests should mirror the existing style in `tests/Sites/ContentFragmentsClientTest.php`.
- Keep `declare(strict_types=1);` and PSR-12 formatting. Run `vendor/bin/phpunit` and `vendor/bin/psalm` (if present) before posting a PR.

## Useful snippets (where to copy patterns)

- Create JSON request:

  - see `src/Core/HttpClient::createJsonRequest()` — use this to set Content-Type and JSON body.
- Check responses & throw:

  - pattern: `$response = $this->client->send($request); if ($response->getStatusCode() >= 300) { throw new ApiException('msg', $response); }`

## Questions / missing info

- If unsure about exact Adobe API paths, prefer following the patterns in `src/Sites/ContentFragmentsClient.php` and call out that endpoint paths may be placeholders (documented in `README.md`).

---

If anything here is unclear or you want additional details (examples for adding a new client, or preferred commit message format), tell me which section to expand. I can iterate on this file.
