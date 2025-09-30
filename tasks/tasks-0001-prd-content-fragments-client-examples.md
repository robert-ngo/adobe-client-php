## Relevant Files

- `src/Sites/ContentFragmentsClient.php` - API client for AEM Content Fragments operations.
- `src/Sdk.php` - SDK entrypoint exposing `contentFragments()` accessor.
- `src/Core/HttpClient.php` - PSR-18 wrapper used by service clients.
- `src/Core/SdkConfig.php` - Base configuration for host, headers, user agent.
- `src/Exceptions/ApiException.php` - Exception for non-2xx responses.
- `tests/Sites/ContentFragmentsClientTest.php` - Unit tests for Content Fragments client.
- `README.md` - Root setup and quick start.
- `tasks/0001-prd-content-fragments-client-examples.md` - PRD with examples driving this task list.

### Notes

- Unit tests use a mock PSR-18 client to validate request method, URI, headers and payload.
- Examples assume Guzzle for PSR-18/PSR-17; alternatives may be used.

## Tasks

- [ ] 1.0 Validate API paths and query parameters against AEM Sites docs
  - [x] 1.1 Verify endpoint paths and HTTP methods map to Fragment Management
  - [x] 1.2 Verify query parameters (limit, offset, model, recursive, search, sort)
  - [x] 1.3 Confirm path encoding rules and base URL handling
  - [x] 1.4 Validate expected success status codes and error handling
  - [x] 1.5 List discrepancies and propose required code/README updates

#### Findings for 1.1 (Endpoint/Method Mapping)

Reference: Adobe AEM Sites API – Fragment Management (`https://developer.adobe.com/experience-cloud/experience-manager-apis/api/stable/sites/#tag/Fragment-Management`)

- List fragments: GET `/api/sites/v1/fragments?path=...` → Implemented in `ContentFragmentsClient::list()`
- Create fragment: POST `/api/sites/v1/fragments` → Implemented in `::create()`
- Get fragment: GET `/api/sites/v1/fragments/{path}` → Implemented in `::get()`
- Edit fragment: PATCH `/api/sites/v1/fragments/{path}` → Implemented in `::update()`
- Delete fragment: DELETE `/api/sites/v1/fragments/{path}` → Implemented in `::delete()`
- Delete and unpublish: POST `/api/sites/v1/fragments/{path}/delete-and-unpublish` → Implemented in `::deleteAndUnpublish()`
- Get preview URLs: GET `/api/sites/v1/fragments/{path}/previews` → Implemented in `::getPreviewUrls()`
- Copy fragment: POST `/api/sites/v1/fragments/{path}/copy` → Implemented in `::copy()`

Status: All requested operations have matching endpoint paths and HTTP methods.

#### Findings for 1.2 (Query Parameters)

Reference: Adobe AEM Sites API – Fragment Management (`https://developer.adobe.com/experience-cloud/experience-manager-apis/api/stable/sites/#tag/Fragment-Management`)

- `path` (required): container path under which to list fragments (e.g., `/content/dam/site`).
- `limit` (optional, integer): max items to return; defaults per server.
- `offset` (optional, integer): pagination offset.
- `model` (optional, string): filter by model path (e.g., `/conf/site/.../models/article`).
- `recursive` (optional, boolean): include items in subfolders.
- `search` (optional, string): text to search in names/metadata.
- `sort` (optional, string): field with direction, e.g., `name:asc`.

Implementation status:

- `ContentFragmentsClient::list()` accepts a flexible `$options` map; it merges with `['path' => $containerPath]` and builds a query string, so all above parameters are supported transparently.
- Tests already cover presence of `path` in query; consider adding tests for `limit`, `offset`, `model`, `recursive`, `search`, `sort` in a later task (see 4.0).

#### Findings for 1.3 (Path Encoding & Base URL)

- The API expects the fragment repository path as a single encoded path segment beneath `/api/sites/v1/fragments/{path}`.
- Updated `ContentFragmentsClient::encodePath()` to keep slashes percent-encoded by using `rawurlencode($path)` directly.
- `HttpClient::createRequest()` correctly composes the base URI from `SdkConfig` with the relative path and applies headers/auth.

#### Findings for 1.4 (Status Codes & Error Handling)

Expected success codes per operation:

- List: 200 OK
- Create: 201 Created
- Get: 200 OK
- Update: 200 OK (or 204 No Content depending on server; 200 covered)
- Delete: 204 No Content
- Delete and Unpublish: 200 OK
- Preview URLs: 200 OK
- Copy: 201 Created

Client behavior:

- All methods treat any status < 300 as success and >= 300 as failure, throwing `ApiException` with the `ResponseInterface` attached. This covers the above success codes.
- JSON parsing uses `JSON_THROW_ON_ERROR`, surfacing malformed JSON as exceptions (acceptable and explicit).

#### Findings for 1.5 (Discrepancies & Proposed Updates)

Discrepancies/risks:

- Tests cover only presence of `path` in list; query parameter variants are not yet tested.
- `SitesClient` and `AssetsClient` endpoints are placeholders and may not reflect official APIs.
- README lacks a dedicated examples section for Content Fragments.

Proposed updates:

- Add unit tests for `limit`, `offset`, `model`, `recursive`, `search`, `sort` in the list operation (see Task 4.0).
- Update README with a Content Fragments usage section (see Task 5.0) and note required headers (e.g., org ID) when applicable.
- Validate actual AEM environment requirements for additional headers or query parameters and extend `SdkConfig` defaults if needed.

- [ ] 2.0 Implement missing edge-case handling (timeouts, JSON decoding failures)
- [ ] 3.0 Add request/response DTOs and type validation for CF payloads
- [ ] 4.0 Extend tests to include negative cases and error responses
- [ ] 5.0 Add examples to README for ContentFragmentsClient usage
- [ ] 6.0 Add CI workflow to run PHPUnit and static analysis
- [ ] 7.0 Document auth, headers, and environment configuration in README
- [ ] 8.0 Create versioned CHANGELOG and semantic versioning policy
