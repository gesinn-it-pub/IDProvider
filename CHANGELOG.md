# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Phan static analysis, wired into CI as a dedicated step on the coverage matrix row.

### Changed

- Bumped `mediawiki/mediawiki-codesniffer` from 45.0.0 to 48.0.0, the highest version
  compatible with the CI container's PHP 8.1, resolving new `ClassAnnotations`,
  `CommentBeforeClass`, `PropertyDocumentation`, and `FunctionComment`/
  `ClassDocumentation` sniff violations along the way.

### Fixed

- Race condition in `IncrementIdGenerator::calculateIncrement()`: concurrent requests
  for the same prefix could read the same increment value or both insert the first row
  for a new prefix, handing out duplicate IDs. Now uses a locked atomic section
  (`SELECT ... FOR UPDATE` inside `doAtomicSection()`), backed by a new `UNIQUE` index
  on `idprovider_increments.prefix` as a defense-in-depth safety net. A schema
  migration merges any duplicate-prefix rows left over from before this fix, keeping
  the highest increment value so no previously issued ID is reused.
- `IdProviderFactory::dbExecute()` opened its own `LoadBalancer` via `newMainLB()`,
  which never saw the active DB domain (including MediaWiki's `unittest_` table
  prefix in tests), causing `{{#idprovider-increment}}` and the `idprovider-increment`
  API module to fail against a test database. Now uses the shared
  `getDBLoadBalancer()` connection instead.
- `Hooks::onUnitTestsList()` globbed `tests/phpunit/*Test.php` non-recursively, so it
  never matched any test file, since all tests live in subdirectories.
- Type mismatches and dead code surfaced by Phan (`base_convert`/`str_pad` argument
  types, nullable `$isUniqueId`, missing `$fname` argument to
  `ILoadBalancerForOwner::disable()`, unreachable pre-MW-1.36 `WikiPage::factory()`
  fallback).
- MediaWiki CodeSniffer (PHPCS) violations: missing doc comments, imprecise `object`
  typehint, non-static closures, and `__METHOD__` used inside closures instead of
  being captured outside them. `.phpcs.xml` now excludes `vendor/`, `build/`, and
  `coverage/` so build artifacts are no longer scanned.
- `FakeIdGenerator::generate()` passed the hex string from `uniqid()` to
  `base_convert()` with a base-10 source, which is invalid for hex digits `a`-`f`
  and triggered an `E_DEPRECATED` "Invalid characters passed for attempted
  conversion" warning on every call (#11). Now correctly converts from base 16.

### Removed

- `phpstan` and `psalm` dev dependencies, which were declared but never configured
  (superseded by Phan).
