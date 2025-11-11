# Library Improvement Recommendations

This document outlines improvements made and additional recommendations for the Adobe Client PHP library.

## ✅ Completed Improvements

### 1. CI/CD Pipeline
**File:** `.github/workflows/tests.yml`

- Automated testing on PHP 8.1, 8.2, 8.3
- Matrix testing with lowest/highest dependencies
- Code coverage reporting with Codecov
- Static analysis with Psalm in CI
- Runs on push and pull requests

**Benefits:**
- Catches bugs before they reach production
- Ensures compatibility across PHP versions
- Maintains code quality standards
- Provides confidence in releases

### 2. Static Analysis Configuration
**File:** `psalm.xml`

- Configured Psalm at error level 4 (strict)
- Ignores vendor directory
- Enables unused code detection
- Set up for comprehensive type checking

**Benefits:**
- Catches type errors at development time
- Improves code reliability
- Prevents runtime type errors
- Documents expected types

### 3. Project Documentation

#### Contributing Guidelines
**File:** `CONTRIBUTING.md`

- Code of conduct reference
- Bug reporting guidelines
- Feature request process
- Pull request workflow
- Development setup instructions
- Coding standards reference
- Commit message conventions

#### Changelog
**File:** `CHANGELOG.md`

- Follows Keep a Changelog format
- Semantic versioning
- Clear categorization (Added, Changed, Fixed)
- Release history tracking

#### Security Policy
**File:** `SECURITY.md`

- Vulnerability reporting process
- Supported versions
- Security best practices
- Disclosure policy

### 4. Developer Experience

#### Makefile
**File:** `Makefile`

Common commands for development:
```bash
make install        # Install dependencies
make test          # Run tests
make test-coverage # Generate coverage report
make psalm         # Run static analysis
make lint          # Check code style
make fix           # Fix code style
make ci            # Run full CI pipeline locally
make quality       # Run all quality checks
```

DDEV-specific commands:
```bash
make ddev-install  # Install via DDEV
make ddev-test     # Test via DDEV
make ddev-psalm    # Static analysis via DDEV
```

### 5. GitHub Templates

#### Issue Templates
- **Bug Report:** Structured format for reporting bugs
- **Feature Request:** Standardized feature proposals

#### Pull Request Template
- Comprehensive checklist
- Type of change categorization
- Testing requirements
- Documentation requirements
- Code quality checklist

## 🚀 Recommended Future Improvements

### Priority 1: Enhanced Error Handling

#### Specific Exception Types
Create specialized exceptions for different error scenarios:

```php
src/Exceptions/
├── ApiException.php                  # Base (exists)
├── AuthenticationException.php       # 401 errors
├── AuthorizationException.php        # 403 errors
├── NotFoundException.php             # 404 errors
├── RateLimitException.php           # 429 errors
├── ValidationException.php          # 400 errors
└── ServerException.php              # 5xx errors
```

**Benefits:**
- More granular error handling
- Easier to catch specific errors
- Better error recovery strategies
- Clearer API documentation

**Implementation Example:**
```php
try {
    $audience = $sdk->audiences()->getAudience($id);
} catch (NotFoundException $e) {
    // Handle not found
} catch (AuthenticationException $e) {
    // Refresh token and retry
} catch (RateLimitException $e) {
    // Implement backoff strategy
}
```

### Priority 2: Response Objects (DTOs)

Replace raw array returns with typed response objects:

```php
src/Segmentation/Responses/
├── Audience.php
├── AudienceList.php
├── SegmentDefinition.php
├── SegmentJob.php
└── ExportJob.php
```

**Example:**
```php
final class Audience
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly AudienceExpression $expression,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? '',
            expression: AudienceExpression::fromArray($data['expression']),
            createdAt: new DateTimeImmutable('@' . ($data['creationTime'] / 1000)),
            updatedAt: new DateTimeImmutable('@' . ($data['updateTime'] / 1000)),
        );
    }
}

// Usage
$audience = $sdk->audiences()->getAudience($id); // Returns Audience object
echo $audience->name;
echo $audience->createdAt->format('Y-m-d');
```

**Benefits:**
- IDE autocomplete
- Type safety
- No typos in array keys
- Self-documenting code
- Easier refactoring

### Priority 3: Authentication Enhancements

#### OAuth2 Support
```php
src/Auth/
├── BearerTokenProvider.php         # Exists
├── OAuth2Provider.php              # New
├── JWTProvider.php                 # New
└── ServiceAccountProvider.php      # New
```

**OAuth2Provider Example:**
```php
final class OAuth2Provider implements AuthProvider
{
    private string $accessToken;
    private DateTimeImmutable $expiresAt;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $tokenUrl,
        private readonly ClientInterface $httpClient,
    ) {}

    public function authenticate(RequestInterface $request): RequestInterface
    {
        if ($this->isTokenExpired()) {
            $this->refreshToken();
        }

        return $request->withHeader('Authorization', 'Bearer ' . $this->accessToken);
    }

    private function isTokenExpired(): bool
    {
        return new DateTimeImmutable() >= $this->expiresAt;
    }

    private function refreshToken(): void
    {
        // Implementation
    }
}
```

### Priority 4: Request/Response Middleware

Add middleware support for cross-cutting concerns:

```php
src/Core/Middleware/
├── MiddlewareInterface.php
├── RetryMiddleware.php
├── LoggingMiddleware.php
├── RateLimitMiddleware.php
└── CacheMiddleware.php
```

**Example:**
```php
interface MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        callable $next
    ): ResponseInterface;
}

final class RetryMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly int $maxRetries = 3,
        private readonly int $delayMs = 1000,
    ) {}

    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                return $next($request);
            } catch (NetworkException $e) {
                if (++$attempt >= $this->maxRetries) {
                    throw $e;
                }
                usleep($this->delayMs * 1000 * $attempt);
            }
        }
    }
}

// Usage
$sdk = new Sdk(
    $httpClient,
    $requestFactory,
    $streamFactory,
    $config,
    $authProvider,
    middleware: [
        new RetryMiddleware(maxRetries: 3),
        new LoggingMiddleware($logger),
        new RateLimitMiddleware(requestsPerSecond: 10),
    ]
);
```

### Priority 5: Logging Support

Add PSR-3 logger integration:

```php
// In HttpClient constructor
public function __construct(
    private readonly Psr18ClientInterface $httpClient,
    private readonly RequestFactoryInterface $requestFactory,
    private readonly StreamFactoryInterface $streamFactory,
    private readonly SdkConfig $config,
    private readonly ?AuthProvider $authProvider = null,
    private readonly ?LoggerInterface $logger = null,
) {}

public function send(RequestInterface $request): ResponseInterface
{
    $this->logger?->debug('Sending request', [
        'method' => $request->getMethod(),
        'uri' => (string) $request->getUri(),
    ]);

    $startTime = microtime(true);

    try {
        $response = $this->httpClient->sendRequest($request);

        $this->logger?->info('Request completed', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);

        return $response;
    } catch (Psr18ClientExceptionInterface $e) {
        $this->logger?->error('Request failed', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'error' => $e->getMessage(),
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);

        throw $e;
    }
}
```

### Priority 6: Additional Adobe Services

Implement more Adobe Experience Platform APIs:

```
src/
├── Catalog/              # Catalog Service API
├── DataAccess/           # Data Access API
├── DataIngestion/        # Data Ingestion API
├── FlowService/          # Flow Service API (sources/destinations)
├── IdentityService/      # Identity Service API
├── Privacy/              # Privacy Service API
├── QueryService/         # Query Service API
├── RealtimeCustomerProfile/  # Real-time Customer Profile API
└── SchemaRegistry/       # Schema Registry API
```

### Priority 7: Code Quality Tools

#### PHP CS Fixer
```bash
composer require --dev friendsofphp/php-cs-fixer
```

**Configuration:** `.php-cs-fixer.dist.php`
```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
```

#### PHPStan (Alternative/Complement to Psalm)
```bash
composer require --dev phpstan/phpstan
```

**Configuration:** `phpstan.neon`
```neon
parameters:
    level: 8
    paths:
        - src
    excludePaths:
        - vendor
```

### Priority 8: Performance Optimizations

#### Connection Pooling
Configure Guzzle with connection persistence:

```php
$httpClient = new GuzzleClient([
    'http_version' => '2.0',
    'connect_timeout' => 3.0,
    'timeout' => 10.0,
    'handler' => HandlerStack::create(),
]);
```

#### Response Caching
```php
src/Core/Cache/
├── CacheInterface.php
├── InMemoryCache.php
└── PsrCacheAdapter.php  # Adapts PSR-6/PSR-16
```

### Priority 9: Developer Tools

#### API Response Debugger
```php
src/Debug/
└── ResponseDebugger.php

// Usage
$debugger = new ResponseDebugger();
$debugger->enable();

$response = $sdk->audiences()->listAudiences();

$debugger->dump(); // Shows raw request/response details
```

#### Request Builder Helpers
```php
src/Builders/
├── AudienceBuilder.php
├── SegmentDefinitionBuilder.php
└── ExportJobBuilder.php

// Usage
$audience = AudienceBuilder::create()
    ->withName('High Value Customers')
    ->withDescription('Customers with lifetime value > $1000')
    ->withPqlExpression('totalValue > 1000')
    ->forSchema('_xdm.context.profile')
    ->enableBatchEvaluation()
    ->build();

$result = $sdk->audiences()->createAudience($audience);
```

### Priority 10: Testing Improvements

#### Integration Tests
```php
tests/Integration/
├── AudiencesIntegrationTest.php
├── SegmentDefinitionsIntegrationTest.php
└── ExportJobsIntegrationTest.php

// With test environment
class AudiencesIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!getenv('ADOBE_API_KEY')) {
            $this->markTestSkipped('Integration tests require Adobe credentials');
        }
    }
}
```

#### Fixtures and Factories
```php
tests/Fixtures/
├── AudienceFixture.php
├── SegmentDefinitionFixture.php
└── ExportJobFixture.php

// Usage in tests
$audience = AudienceFixture::create([
    'name' => 'Test Audience',
    'expression' => ['type' => 'PQL', 'value' => 'age > 18'],
]);
```

### Priority 11: Documentation Enhancements

#### API Reference Documentation
Use phpDocumentor or Sami:

```bash
composer require --dev phpdocumentor/phpdocumentor
vendor/bin/phpdoc -d src -t docs/api
```

#### Interactive Examples
Create Jupyter-style notebooks or interactive documentation:
- Usage recipes
- Common patterns
- Migration guides
- Troubleshooting guides

#### Video Tutorials
- Getting started
- Authentication setup
- Common use cases
- Advanced patterns

### Priority 12: Packaging & Distribution

#### Packagist Badges
Add to README.md:
```markdown
[![Latest Stable Version](https://poser.pugx.org/robert-ngo/adobe-client-php/v/stable)](https://packagist.org/packages/robert-ngo/adobe-client-php)
[![Total Downloads](https://poser.pugx.org/robert-ngo/adobe-client-php/downloads)](https://packagist.org/packages/robert-ngo/adobe-client-php)
[![License](https://poser.pugx.org/robert-ngo/adobe-client-php/license)](https://packagist.org/packages/robert-ngo/adobe-client-php)
[![PHP Version](https://img.shields.io/packagist/php-v/robert-ngo/adobe-client-php)](https://packagist.org/packages/robert-ngo/adobe-client-php)
```

#### GitHub Releases
- Automated releases via GitHub Actions
- Release notes generation
- Asset uploads (if needed)

## 📊 Implementation Priority Matrix

| Priority | Improvement | Impact | Effort | ROI |
|----------|------------|--------|--------|-----|
| P1 | Specific Exception Types | High | Low | ⭐⭐⭐⭐⭐ |
| P1 | CI/CD Pipeline | High | Low | ⭐⭐⭐⭐⭐ |
| P2 | Response Objects (DTOs) | High | Medium | ⭐⭐⭐⭐ |
| P2 | Logging Support | Medium | Low | ⭐⭐⭐⭐ |
| P3 | OAuth2 Provider | Medium | Medium | ⭐⭐⭐⭐ |
| P3 | Middleware System | Medium | High | ⭐⭐⭐ |
| P4 | Additional Adobe Services | High | High | ⭐⭐⭐ |
| P4 | Code Quality Tools | Medium | Low | ⭐⭐⭐⭐ |
| P5 | Performance Optimizations | Medium | Medium | ⭐⭐⭐ |
| P5 | Developer Tools | Low | Medium | ⭐⭐⭐ |

## 🎯 Quick Wins (Easy & High Impact)

1. **Specific Exception Types** - 2-4 hours
2. **PSR-3 Logging** - 2-3 hours
3. **PHP CS Fixer** - 1-2 hours
4. **Packagist Badges** - 30 minutes
5. **Integration Test Structure** - 2-3 hours

## 📝 Next Steps

1. Review and prioritize improvements based on your needs
2. Create GitHub issues for accepted improvements
3. Implement high-priority items first
4. Update CHANGELOG.md as features are added
5. Release new versions following semver

## 🤝 Community Engagement

- Set up GitHub Discussions for Q&A
- Create a roadmap in GitHub Projects
- Engage with Adobe developer community
- Share on Packagist, Reddit, Twitter/X
- Write blog posts about features
- Present at PHP conferences

---

**Note:** All improvements should maintain backward compatibility unless releasing a new major version. Breaking changes should be clearly documented and include migration guides.
