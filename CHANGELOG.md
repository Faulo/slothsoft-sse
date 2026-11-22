# Changelog

## [Unreleased]

## [1.12.1] - 2026-11-23

### Fixed

- Declare `Server::dispatchEvent()` with string parameters for the event type and data so subclasses such as Slothsoft Chat can override it with the same signature. Callers of the base server must pass strings.

## [1.12.0] - 2026-11-22

### Changed

- Require the PHP MySQLi extension in Composer for database-backed event streams.
- Require integer event cursors and string server names in the typed server APIs. Event dispatch accepts a nullable string type and scalar, stringable, or null data.
- Pass the database table explicitly to the server installation hook.
- Implement the Core string-writer interface in the event generator.

### Fixed

- Install the correct event table schema when creating a Unison or VCT server table.
- Stream Unison playback events without iterating the event generator by reference. Keep the original payload when playback progress cannot be calculated.
- Return an empty event list when a VCT server has no table and escape the sender ID before filtering events.
- Enable MySQLi in GitHub Actions test jobs so Windows runners can exercise the database integration.

### Removed

- Remove `EventGenerator::toFileName()` and `EventStreamBuilder::buildStreamFileStat()`. Farah obtains the filename and file statistics from the stream builder strategy.

## [1.11.3] - 2026-10-26

### Changed

- Raise the minimum versions to Core 1.15.0 and Farah 1.29.7.
- Add a DDEV development environment and PHPStan level 5 checks. Expand the GitHub test matrix to Linux on x64 and ARM64 and to Windows. Run the Jenkins integration matrix on Linux and Windows.

### Fixed

- Handle null database query results when reading event IDs and event rows. Return `null` when dispatching through a server without a database table.

## [1.11.2] - 2026-04-02

### Changed

- Lower the minimum PHP version from 8.3 to 8.2 and raise the minimum Farah version to 1.28.2.

## [1.11.1] - 2026-04-02

### Changed

- Raise the minimum versions to PHP 8.3, Farah 1.28.1, and `psr/http-message` 1.1.

## [1.11.0] - 2026-04-02

### Changed

- Raise the Composer minimum PHP version from 7.4 to 8.0 and the minimum Farah version to 1.28.0.

## [1.10.16] - 2026-03-13

### Changed

- Add PHP 8.4 and 8.5 to the GitHub test matrix and raise the minimum versions to Core 1.14.19 and Farah 1.27.1.

### Fixed

- Declare the event-line formatter's optional data argument explicitly nullable for newer PHP versions.

## [1.0.15] - 2026-01-09

### Fixed

- Report browser SSE receive errors as console warnings and include the event object.

## [1.0.14] - 2026-01-01

### Removed

- Remove the diagnostic exception log written beside the pull builder's source file.

## [1.0.13] - 2026-01-01

### Added

- Publish the `/api/pull` asset with `name` and `lastId` parameters.

### Changed

- Raise the minimum Core version to 1.14.9.
- Make `EventStreamBuilder` and `WaitingGenerator` final.

### Fixed

- Pass the SSE chunk writer directly to Core's stream adapter with buffering disabled.

## [1.0.12] - 2025-10-24

### Changed

- Export the JavaScript SSE client as the module's default export. Update imports that use the named `Client` export.

## [1.0.11] - 2025-10-24

### Added

- Add the PHP pull builder and parameter filter, along with a test asset and browser tests for the client's `open` and `start` events.

### Changed

- Adopt Farah Testing for the module tests and raise the minimum Farah version to 1.24.0.

## [1.0.10] - 2025-10-18

### Fixed

- Implement `isDifferentFromDefault()` on the SSE result builder for compatibility with Farah's result strategy interface.

### Changed

- Raise the minimum Farah version to 1.22.2.

## [1.0.9] - 2025-09-29

### Added

- Publish the `/api/webrtc-client` asset with the WebRTC client, browser adapter, and user helper.

### Changed

- Raise the minimum Farah version to 1.21.3.

## [1.0.8] - 2025-09-20

### Fixed

- Load the DOM helper for `/api/client` from Farah.

### Changed

- Raise the minimum Farah version to 1.20 and remove the checked-in Composer lockfile.

## [1.0.7] - 2025-09-17

### Changed

- Switch PHP autoloading from PSR-0 to PSR-4, move the module bootstrap to `src/bootstrap.php`, and add native types to the server and stream classes.
- Adopt version 1.1 of the Farah manifest format and load `/js/Client` from `/api/client`.
- Raise the minimum Farah version to 1.18, migrate to PHPUnit 9.6, and add GitHub and Jenkins test pipelines.

## [1.0.6] - 2025-07-04

### Changed

- Move API documentation generation to GitHub Actions and publish the result on GitHub Pages.
- Refresh the dependency lockfile, including Core 1.13.0, Farah 1.14.2, and Symfony Process 5.4.47.

## [1.0.5] - 2024-10-05

### Changed

- Raise the minimum PHP version to 7.4 and refresh the dependency lockfile, including Farah 1.12.3.

## [1.0.4] - 2024-09-29

### Changed

- Change the package license from WTFPL to MIT and update the locked Core version to 1.11.6.

## [1.0.3] - 2024-09-23

### Changed

- Add the Composer lockfile and regenerate the API documentation with class and source indexes.

## [1.0.2] - 2024-04-01

### Changed

- Refresh generated API documentation, including the namespace pages and reports.

## [1.0.1] - 2024-04-01

### Changed

- Let Composer infer the package version from Git tags and add the project homepage to the package metadata.
- Remove explicit names from the import and script declarations in the `/api/client` manifest fragment.

## [1.0.0] - 2018-08-10

### Added

- Publish the initial Farah SSE module with a PHP 7.2 minimum, including database-backed servers, chunked `text/event-stream` output with heartbeat comments, and browser client code.

[Unreleased]: https://github.com/Faulo/slothsoft-sse/compare/1.12.1...HEAD
[1.12.1]: https://github.com/Faulo/slothsoft-sse/compare/1.12.0...1.12.1
[1.12.0]: https://github.com/Faulo/slothsoft-sse/compare/1.11.3...1.12.0
[1.11.3]: https://github.com/Faulo/slothsoft-sse/compare/1.11.2...1.11.3
[1.11.2]: https://github.com/Faulo/slothsoft-sse/compare/1.11.1...1.11.2
[1.11.1]: https://github.com/Faulo/slothsoft-sse/compare/1.11.0...1.11.1
[1.11.0]: https://github.com/Faulo/slothsoft-sse/compare/1.10.16...1.11.0
[1.10.16]: https://github.com/Faulo/slothsoft-sse/compare/1.0.15...1.10.16
[1.0.15]: https://github.com/Faulo/slothsoft-sse/compare/1.0.14...1.0.15
[1.0.14]: https://github.com/Faulo/slothsoft-sse/compare/1.0.13...1.0.14
[1.0.13]: https://github.com/Faulo/slothsoft-sse/compare/1.0.12...1.0.13
[1.0.12]: https://github.com/Faulo/slothsoft-sse/compare/1.0.11...1.0.12
[1.0.11]: https://github.com/Faulo/slothsoft-sse/compare/1.0.10...1.0.11
[1.0.10]: https://github.com/Faulo/slothsoft-sse/compare/1.0.9...1.0.10
[1.0.9]: https://github.com/Faulo/slothsoft-sse/compare/1.0.8...1.0.9
[1.0.8]: https://github.com/Faulo/slothsoft-sse/compare/1.0.7...1.0.8
[1.0.7]: https://github.com/Faulo/slothsoft-sse/compare/1.0.6...1.0.7
[1.0.6]: https://github.com/Faulo/slothsoft-sse/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/Faulo/slothsoft-sse/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/Faulo/slothsoft-sse/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/Faulo/slothsoft-sse/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/Faulo/slothsoft-sse/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/Faulo/slothsoft-sse/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/Faulo/slothsoft-sse/tree/1.0.0
