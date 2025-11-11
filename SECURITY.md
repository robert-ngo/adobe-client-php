# Security Policy

## Supported Versions

We release patches for security vulnerabilities for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability, please send an email to [security email].

Please include the following information:

- Type of issue (e.g., buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

You can expect:

- A response within 48 hours acknowledging receipt of your report
- An assessment of the vulnerability and its impact
- A timeline for a fix and disclosure
- Credit for the discovery (if desired)

## Security Best Practices

When using this SDK:

1. **Never commit credentials** to version control
   - Use environment variables for API keys and tokens
   - Add `.env` files to `.gitignore`

2. **Keep dependencies updated**
   - Regularly run `composer update` to get security patches
   - Monitor security advisories for Guzzle and other dependencies

3. **Use HTTPS only**
   - Always configure `SdkConfig` with HTTPS URLs
   - Never disable SSL/TLS verification in production

4. **Validate and sanitize input**
   - Validate user input before passing to API methods
   - Be cautious with user-provided data in queries

5. **Use authentication properly**
   - Rotate API tokens regularly
   - Use appropriate authentication scopes
   - Implement token expiry and refresh mechanisms

6. **Handle errors securely**
   - Don't expose sensitive information in error messages
   - Log errors securely without including credentials

## Disclosure Policy

When we receive a security bug report, we will:

1. Confirm the problem and determine affected versions
2. Audit code to find similar problems
3. Prepare fixes for all supported versions
4. Release new versions with the fixes
5. Publish a security advisory

We aim to disclose vulnerabilities within 90 days of receiving the report or when a fix is available, whichever comes first.

## Comments on This Policy

If you have suggestions on how this process could be improved, please submit a pull request.
