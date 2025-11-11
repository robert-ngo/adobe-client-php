## Description
<!-- Provide a clear and concise description of your changes -->

## Type of Change
<!-- Mark the relevant option with an 'x' -->

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Refactoring (no functional changes)
- [ ] Test improvements
- [ ] Dependency update

## Related Issues
<!-- Link to related issues using #issue_number -->

Closes #
Related to #

## Changes Made
<!-- Provide a detailed list of changes -->

-
-
-

## Testing
<!-- Describe the tests you ran and how to reproduce them -->

### Test Coverage
- [ ] All tests pass locally (`make test`)
- [ ] Added new tests for new functionality
- [ ] Updated existing tests for changed functionality
- [ ] Static analysis passes (`make psalm`)

### Manual Testing
<!-- Describe any manual testing performed -->

```php
// Example of how you tested this
$sdk = new Sdk(...);
$result = $sdk->yourClient()->yourMethod();
```

## Documentation
<!-- Describe documentation changes -->

- [ ] Updated README.md (if needed)
- [ ] Updated CLAUDE.md (if needed)
- [ ] Updated CHANGELOG.md
- [ ] Added/updated PHPDoc comments
- [ ] Added/updated code examples

## Code Quality Checklist
<!-- Verify your code meets quality standards -->

- [ ] Code follows PSR-12 coding standards
- [ ] All files have `declare(strict_types=1);`
- [ ] All methods have proper type hints
- [ ] All public methods have PHPDoc comments
- [ ] Error handling follows existing patterns
- [ ] No new Psalm warnings/errors introduced
- [ ] Follows patterns established in CONTRIBUTING.md

## Breaking Changes
<!-- If this is a breaking change, describe the impact -->

- [ ] This PR introduces breaking changes
- [ ] Migration guide added/updated (if applicable)

**Breaking Change Details:**
<!-- Describe what breaks and how users should migrate -->

## Screenshots / Examples
<!-- If applicable, add screenshots or code examples -->

## Additional Notes
<!-- Any additional information that reviewers should know -->

## Reviewer Checklist
<!-- For reviewers -->

- [ ] Code quality meets standards
- [ ] Tests are comprehensive
- [ ] Documentation is clear and complete
- [ ] No security concerns
- [ ] Performance considerations addressed
- [ ] Breaking changes properly documented
