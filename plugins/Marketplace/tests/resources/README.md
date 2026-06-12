# Marketplace test fixtures

Tests must not hit the live Marketplace (CI parallelism trips the WAF). This directory holds hand-maintained, anonymised response stubs; under `PIWIK_TEST_MODE` they are served via `plugins/Marketplace/tests/Framework/Mock/FixtureRepository.php`, registered as an `Http.sendHttpRequest` listener from `plugins/Marketplace/config/test.php`. `Service.php` is unaware of the interception — the network is short-circuited at the HTTP layer.

The fixtures are intentionally synthetic — third-party plugin/theme developer data has been stripped — so they should be edited by hand when a new endpoint or scenario is needed rather than re-recorded from production.

## How lookup works

`FixtureRepository` builds a canonical key from each request: `path + sorted query (significant params only) + access_token from POST`. Environment noise (`piwik` matching the current major, `php`, `mysql`, `prefer_stable`, `release_channel`, `num_users`, `num_websites`) and empty params are dropped. `manifest.json` maps the resulting keys to fixture filenames.

A miss on a known marketplace host (`plugins.matomo.org` / `plugins.piwik.org` / themes equivalents) writes a stderr line prefixed `[Marketplace FixtureRepository]` (deduplicated per canonical key) and lets the real HTTP transport proceed. No PHP deprecation or exception is raised — that's deliberate, so external plugin CIs that hit un-cached Marketplace URLs are never broken by this hook. The miss will graduate to a hard throw once all in-tree and external plugin tests provide fixtures (see PR #24624). Hosts outside the marketplace list are not intercepted.

Manifest entry value formats:

```json
{
  "/api/2.0/info": "v2.0_info.json",
  "/api/2.0/consumer": {"file": "v2.0_consumer-access_token-notexistingtoken.json", "status": 401}
}
```

JSON fixtures are minified by the interceptor before being returned, so pretty-printed files on disk still satisfy tests that assert on the raw response shape (e.g. `assertStringStartsWith('{"plugins"', ...)`).

## Adding a new fixture by hand

1. Drop a JSON file into this directory with a descriptive name.
2. Add a `manifest.json` entry mapping the canonical URL key to the filename. Add `{"file": ..., "status": <code>}` for non-200 responses.
3. Keep the payload minimal — only fields the calling code actually reads. No real author names, emails, owner handles, marketing copy, or shop variations.
4. Run the affected test group to confirm.

## Binary fixtures

The single `.zip` fixture (`TreemapVisualization-4.0.2.zip`) is committed directly because it is small (~83 KB) and `LastForcedInstall` needs a real Piwik-4-compatible plugin archive to extract. Keep binary fixtures tiny; move them to Git LFS rather than this directory if you ever need anything larger.

## Per-test overrides

Tests that need a different response for a known endpoint (e.g. `createAccount` returning 409 instead of 200) register a temporary override:

```php
FixtureRepository::setOverride('/api/2.0/createAccount', ['file' => 'v2.0_createAccount_duplicate-email.json', 'status' => 409]);
// ... run assertion ...
FixtureRepository::clearOverrides();
```

Overrides resolve fixture filenames against the Marketplace base directory.

## Extending the manifest from another plugin

A plugin outside Marketplace can contribute its own fixtures without editing this directory. From the plugin's own `config/test.php`:

```php
use Piwik\Plugins\Marketplace\tests\Framework\Mock\FixtureRepository;

FixtureRepository::registerManifestDirectory(__DIR__ . '/../tests/resources');
```

- The directory must contain its own `manifest.json` plus fixture files (same shape as this one).
- Manifest entries are merged with the Marketplace base manifest. Later registrations win on key collision, so a plugin can redirect an existing canonical URL to its own fixture.
- Fixture filenames are resolved against the directory the winning entry came from — so plugin fixtures stay in the plugin's directory.
- Per-test overrides set via `setOverride()` still take highest precedence and resolve against the Marketplace base directory.
- For isolated unit tests that register temporary directories, call `FixtureRepository::clearRegisteredManifestDirectories()` in `tearDown()`.
