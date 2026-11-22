# Changelog

## [Unreleased]

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

[Unreleased]: https://github.com/Faulo/slothsoft-sse/compare/1.12.0...HEAD
[1.12.0]: https://github.com/Faulo/slothsoft-sse/compare/1.11.3...1.12.0
