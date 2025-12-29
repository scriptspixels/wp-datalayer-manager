# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-XX

### Added
- Automatic detection of WordPress context (page type, post info, categories, tags, etc.)
- Frontend injection of dataLayer variables using `.push()` method
- Admin interface to view detected variables
- Support for all WordPress page types (home, blog, post, page, category, tag, archive, search, 404)
- User login status detection
- Site information variables
- Developer hooks for extensibility (`datalayer_manager_variables` filter)
- Debug mode for troubleshooting
- Minimum WordPress and PHP version checks
- Internationalization support (textdomain loading)
- Comprehensive documentation

### Security
- All inputs sanitized
- All outputs escaped
- Capability checks on admin routes
- Safe error handling

### Performance
- Lightweight implementation
- No database queries required
- Efficient context detection

