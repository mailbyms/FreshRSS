# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FreshRSS is a self-hosted RSS feed aggregator written in PHP. It supports multiple database backends (SQLite, MySQL/MariaDB, PostgreSQL), multi-user authentication, extensions, and mobile APIs (Google Reader API and Fever API).

## Common Development Commands

### PHP Development

```bash
# Install dependencies
composer install

# Run all PHP tests (lint, unit tests, code style, static analysis)
composer run-script test

# Run only unit tests
composer run-script phpunit

# Run PHP linter (syntax check)
composer run-script php-lint

# Run PHTML template linter
composer run-script phtml-lint

# Check code style (PHP_CodeSniffer)
composer run-script phpcs

# Fix code style issues automatically
composer run-script phpcbf

# Run static analysis (PHPStan level 7)
composer run-script phpstan

# Run stricter PHPStan analysis (level 8, progressive adoption)
composer run-script phpstan-next

# Format translation files
composer run-script translations
```

### Frontend Development (Node.js/npm)

```bash
# Install npm dependencies
npm install

# Run all frontend tests (ESLint, Stylelint, Markdownlint)
npm test

# Run ESLint for JavaScript
npm run eslint

# Fix JavaScript issues
npm run eslint_fix

# Run Stylelint for CSS/SCSS
npm run stylelint

# Fix CSS/SCSS issues
npm run stylelint_fix

# Run Markdownlint for Markdown files
npm run markdownlint

# Fix Markdown issues
npm run markdownlint_fix

# Generate RTL CSS files
npm run rtlcss

# Fix all frontend issues
npm run fix
```

### Make Commands (Docker-based)

```bash
# Build Docker image
make build

# Start development environment (port 8080)
make start

# Stop development environment
make stop

# Run tests via Docker
make test

# Run linter via Docker
make lint

# Fix linter errors via Docker
make lint-fix

# Run all tests (PHP + JS + typos)
make test-all

# Fix all issues
make fix-all

# Refresh feeds
make refresh
```

### CLI Commands

```bash
# Prepare data directories
./cli/prepare.php

# Install/configure FreshRSS
./cli/do-install.php --default_user admin --auth_type form --db-type sqlite

# Create user
./cli/create-user.php --user username --password 'password'

# List users
./cli/list-users.php

# Get user info
./cli/user-info.php --user username -h

# Fetch feeds for user
./cli/actualize-user.php --user username

# Export/import data
./cli/export-opml-for-user.php --user username > export.opml.xml
./cli/import-for-user.php --user username --filename /path/to/file.opml
```

## Architecture

### Directory Structure

```
/app
  /Controllers    - MVC controllers (feed, entry, auth, user, etc.)
  /Models         - Domain models and DAOs (Data Access Objects)
  /Services       - Business logic services
  /SQL            - Database schema and migrations
  /views          - PHP templates (.phtml files)
  /i18n           - Internationalization files (per-language)
  /layout         - Layout templates
  /Utils          - Utility classes
/cli              - Command-line tools
/lib              - Third-party libraries and core extensions
  /SimplePie      - RSS/Atom parsing library
  /MINZ           - MVC framework
  /core-extensions - Built-in extensions
/p                - Public web root (only folder exposed to web)
  /api            - API endpoints (Google Reader, Fever)
  /themes         - CSS/JS themes
  /scripts        - JavaScript files
/data             - User data, configuration, logs (not web-accessible)
/extensions       - User-installed extensions
/tests            - PHPUnit tests
```

### MVC Framework (MINZ)

FreshRSS uses a custom MVC framework called MINZ:

- **Controllers**: Handle HTTP requests, located in `/app/Controllers/*Controller.php`
- **Models**: Domain objects and database access in `/app/Models/`
  - Entity classes: `Feed.php`, `Entry.php`, `Category.php`, `Tag.php`
  - DAO classes: `*DAO.php` with database-specific variants (`*DAOSQLite.php`, `*DAOPGSQL.php`)
- **Views**: PHP templates in `/app/views/` using `.phtml` extension
- **Configuration**: `Minz_Configuration` class manages system and user configs

### Database Layer

- **DAO Pattern**: Each entity has a DAO class with database-specific implementations
- **Supported databases**: SQLite (default), MySQL/MariaDB, PostgreSQL
- **PDO**: Uses PHP PDO for database abstraction
- **Migrations**: Located in `/app/SQL/`

### Key Classes

- `FreshRSS` (extends `Minz_FrontController`): Main application entry point
- `FreshRSS_Context`: Manages system and user configuration context
- `FreshRSS_Auth`: Authentication system (form, HTTP auth, OpenID Connect)
- `FreshRSS_Entry`, `FreshRSS_Feed`, `FreshRSS_Category`: Core domain models
- `FreshRSS_ExtensionManager`: Extension system management

### Extension System

- Core extensions: `/lib/core-extensions/`
- User extensions: `/extensions/`
- Extension API: Hooks system via `Minz_ExtensionManager::callHook()`
- Extensions can modify UI, add features, and integrate with external services

### API Support

- **Google Reader API**: Full-featured API for mobile clients (`/p/api/`)
- **Fever API**: Limited compatibility API
- **WebSub (PubSubHubbub)**: Real-time push notifications

## Coding Standards

### PHP

- **Indentation**: Tabs (not spaces)
- **Line length**: 165 chars soft limit, 190 hard limit
- **Brace style**: K&R (Kernighan and Ritchie) - opening brace on same line
- **Comparison**: Use strict comparisons (`===`, `!==`) when possible
- **Naming**: CamelCase for classes, camelCase for methods/variables
- **PHPDoc**: Required for public methods

### JavaScript

- **Style**: ESLint with standard config
- **Indentation**: Tabs

### CSS/SCSS

- **Style**: Stylelint with recommended config
- **Indentation**: Tabs
- **RTL**: Use `npm run rtlcss` to generate RTL versions

### Translations

- Location: `/app/i18n/` with per-language directories
- Format: PHP arrays returning translation strings
- Management: Use `./cli/manipulate.translation.php` or Make targets

## Testing

### PHP Tests

```bash
# Run all PHP tests
composer run-script phpunit

# Run specific test file
vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/app/Models/EntryTest.php

# Run tests with verbose output
vendor/bin/phpunit --bootstrap ./tests/bootstrap.php --verbose ./tests
```

### Test Structure

- Tests mirror the app structure in `/tests/`
- Bootstrap: `/tests/bootstrap.php`
- PHPUnit 9.x required

### Static Analysis

- **PHPStan**: Level 7 (strict rules enabled)
- **Progressive adoption**: `phpstan-next` for level 8 on select files
- Configuration: `/phpstan.neon`

## Configuration Files

- **System config**: `/config.default.php` (defaults), `/data/config.php` (custom)
- **User config**: `/config-user.default.php`, `/data/users/*/config.php`
- **Constants**: `/constants.php` (do not edit), `/constants.local.php` (custom)
- **Code style**: `/phpcs.xml`, `/phpstan.neon`, `/.editorconfig`
- **Linting**: `/.eslintrc.json`, `/.stylelintrc.json`, `/.markdownlint.json`

## Development Workflow

1. **Setup**: `composer install && npm install`
2. **Code**: Follow MVC patterns, use DAOs for database access
3. **Test**: Run `composer run-script test && npm test` before committing
4. **Lint**: Use `make fix-all` to auto-fix common issues
5. **Database changes**: Add migrations in `/app/SQL/` and update DAOs for all backends

## Important Notes

- **Web root**: Only expose `/p/` folder to the web
- **Data security**: `/data/` contains sensitive data, never expose
- **Database migrations**: Update all DAO variants (MySQL, SQLite, PostgreSQL)
- **Translations**: Update all language files when adding new strings
- **Extensions**: Test with both core and third-party extensions enabled
