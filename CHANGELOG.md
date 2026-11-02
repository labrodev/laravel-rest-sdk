# Changelog

All notable changes to `labrodev/rest-sdk` 
documented in this file.

## [1.00] - 2025-01-15

### Added

- General contracts and services to organize the Wrapper to communicate with any external REST Api

* Contracts
* Exceptions
* Factories
* Services

## [1.03] - 2025-07-16

### Added

Body parameter in $handler→run() function to manage body of request.

## [1.1.0] - 2026-11-02

### Added

- `connectTimeout` and `timeout` (seconds) on the `Handler` constructor, applied to every request. `null` (default) keeps Laravel's defaults.
- `retryDelaySeconds` on the `Handler` constructor (default `5`) to control the sleep before retrying a 408 response; `0` retries immediately.

### Fixed

- A request retried after a 408 response now returns the retried response instead of the original failed one.
- `requestLimitAttempts` on the `Handler` is now passed to the client (it was ignored).
- The 408 retry limit allowed one retry more than configured; `requestLimitAttempts` is now the exact maximum number of retries (`0` disables retrying).
