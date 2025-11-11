# Contributing to Adobe Client PHP

Thank you for considering contributing to this project! This document outlines the process and guidelines.

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues. When creating a bug report, include:

- **Clear title and description**
- **Steps to reproduce** the problem
- **Expected behavior** vs actual behavior
- **PHP version** and relevant environment details
- **Code samples** demonstrating the issue

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Clear title and description** of the suggested feature
- **Use case** explaining why this would be useful
- **Proposed implementation** if you have ideas

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Follow the coding standards** (PSR-12, strict types, PHPDoc)
3. **Add tests** for any new functionality
4. **Ensure all tests pass**: `vendor/bin/phpunit`
5. **Run static analysis**: `vendor/bin/psalm`
6. **Update documentation** as needed
7. **Write clear commit messages** following conventional commits format

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- DDEV (optional, recommended)

### Setup with DDEV

```bash
# Start DDEV environment
ddev start

# Install dependencies
ddev composer install

# Run tests
ddev phpunit

# Run static analysis
ddev exec vendor/bin/psalm
```

### Setup without DDEV

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run static analysis
vendor/bin/psalm
```

## Coding Standards

### PSR Compliance

- **PSR-4** for autoloading
- **PSR-12** for coding style
- **PSR-7** for HTTP messages
- **PSR-18** for HTTP client abstraction

### PHP Standards

- Always use `declare(strict_types=1);` at the top of each file
- All classes should be `final` unless designed for extension
- Use typed properties and return types
- Add comprehensive PHPDoc comments

### Client Implementation Patterns

When adding new service clients, follow these patterns:

```php
<?php

declare(strict_types=1);

namespace Adobe\Client\YourService;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Exceptions\ApiException;

/**
 * Client for Adobe YourService API.
 *
 * Brief description of what this service does.
 */
final class YourServiceClient
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Method description.
     *
     * @param array<string,mixed> $options Query parameters
     * @return array<mixed>
     * @throws ApiException
     */
    public function yourMethod(array $options = []): array
    {
        $qs = http_build_query($options);
        $uri = '/api/path' . ($qs !== '' ? ('?' . $qs) : '');
        $request = $this->client->createRequest('GET', $uri);
        $response = $this->client->send($request);

        if ($response->getStatusCode() >= 300) {
            throw new ApiException('Failed to perform action', $response);
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }
}
```

### Testing Patterns

Use `MockPsr18Client` for unit tests:

```php
<?php

declare(strict_types=1);

namespace Adobe\Client\Tests\YourService;

use Adobe\Client\Core\HttpClient;
use Adobe\Client\Tests\Http\MockPsr18Client;
use Adobe\Client\YourService\YourServiceClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;

final class YourServiceClientTest extends TestCase
{
    public function testYourMethod(): void
    {
        $factory = new HttpFactory();
        $mockClient = new MockPsr18Client(function ($request) use ($factory) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertStringContainsString('/api/path', (string) $request->getUri());

            return $factory->createResponse(200)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($factory->createStream('{"result":"success"}'));
        });

        $httpClient = new HttpClient($mockClient, $factory, $factory, $config);
        $client = new YourServiceClient($httpClient);

        $result = $client->yourMethod();

        $this->assertIsArray($result);
        $this->assertSame('success', $result['result']);
    }
}
```

## Commit Message Guidelines

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(segmentation): add AudiencesClient for audience management
fix(auth): correct token expiry handling in BearerTokenProvider
docs(readme): update installation instructions for PHP 8.3
test(sites): add tests for ContentFragmentsClient error handling
```

## Release Process

1. Update version in `composer.json`
2. Update `CHANGELOG.md` with release notes
3. Create a tagged release on GitHub
4. Packagist will automatically pick up the new version

## Questions?

Feel free to open an issue for any questions or concerns.
