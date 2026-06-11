# Marketplace test fixtures

Tests must not hit the live Marketplace (CI parallelism trips the WAF). This directory holds hand-maintained, anonymised response stubs; `Service::download()` serves them automatically whenever `PIWIK_TEST_MODE` is on, via `plugins/Marketplace/tests/Framework/Mock/FixtureRepository.php`.

The fixtures are intentionally synthetic — third-party plugin/theme developer data has been stripped — so they should be edited by hand when a new endpoint or scenario is needed rather than re-recorded from production.

## How lookup works

`FixtureRepository` builds a canonical key from each request: `path + sorted query (significant params only) + access_token from POST`. Environment noise (`piwik` matching the current major, `php`, `mysql`, `prefer_stable`, `release_channel`, `num_users`, `num_websites`) and empty params are dropped. `manifest.json` maps the resulting keys to fixture filenames.

A miss inside a known marketplace host (`plugins.matomo.org` / `plugins.piwik.org` / themes equivalents) throws `\Exception` and logs to stderr — no silent passes, no outbound HTTP. Hosts outside that list are not intercepted.

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
