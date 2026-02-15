# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-02-15

### Added
- ✨ Complete rewrite from scratch
- 🔧 ENV-based configuration (no hardcoded values)
- 🏷️ ETag support for efficient `304 Not Modified` responses
- 📅 Last-Modified header with `If-Modified-Since` validation
- 🎯 Regex-based path exclusion/inclusion
- 🔒 SSL verification toggle
- 🐢 Configurable proxy timeout
- 📊 Debug mode with detailed logging
- 🗜️ Optional gzip compression
- 🚫 HTTP method filtering (GET/HEAD default)
- 📋 Comprehensive README with examples
- 🐳 Docker Compose with health checks
- 🔄 GitHub Actions for automated Docker Hub publishing
- 🧪 Multi-platform Docker builds (amd64 + arm64)

### Changed
- 🔄 Replaced `file_get_contents()` with `curl` for better performance
- 🎨 Improved domain rewriting with multiple replacement patterns
- 📦 Upgraded to PHP 8.3 base image
- 🔧 Better error handling and HTTP status codes
- 🏗️ Structured .htaccess with security headers

### Fixed
- ✅ No error handling → Robust curl error checking
- ✅ Unsafe string replacement → Context-aware domain rewriting
- ✅ Missing headers → Full header forwarding
- ✅ No timeout control → Configurable timeouts
- ✅ No SSL verification → Optional SSL validation

### Security
- 🔒 Added X-Frame-Options, X-Content-Type-Options, X-XSS-Protection headers
- 🚫 Disabled directory listing
- 🔐 Deny access to hidden files

## [1.0.0] - 2023-XX-XX

### Added
- 🎉 Initial release
- Basic proxy functionality with `file_get_contents()`
- Simple domain replacement with `str_replace()`
- Docker container support

[2.0.0]: https://github.com/andreaskasper/docker-simpleproxy/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/andreaskasper/docker-simpleproxy/releases/tag/v1.0.0
