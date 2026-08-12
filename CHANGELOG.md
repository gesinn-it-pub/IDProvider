# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Phan static analysis, wired into CI as a dedicated step on the coverage matrix row.

### Fixed

- Type mismatches and dead code surfaced by Phan (`base_convert`/`str_pad` argument
  types, nullable `$isUniqueId`, missing `$fname` argument to
  `ILoadBalancerForOwner::disable()`, unreachable pre-MW-1.36 `WikiPage::factory()`
  fallback).

### Removed

- `phpstan` and `psalm` dev dependencies, which were declared but never configured
  (superseded by Phan).
