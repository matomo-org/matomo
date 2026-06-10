# Marketplace test fixtures

Tests must not hit the live Marketplace (CI is currently blocked by the WAF). This directory holds recorded responses; `Service::download()` serves them automatically whenever `PIWIK_TEST_MODE` is on, via
`plugins/Marketplace/tests/Framework/Mock/FixtureRepository.php`.

## How lookup works

`FixtureRepository` builds a canonical key from each request: path + sorted query (significant params only) + `access_token` from POST data. Environment noise (`piwik`, `php`, `mysql`, `prefer_stable`, `release_channel`, `num_users`, `num_websites`) and empty params are dropped. `manifest.json` maps those keys to fixture filenames. A miss throws (no silent passes / no outbound HTTP).

Entry value formats:

```json
{
  "/api/2.0/info": "v2.0_info.json",
  "/api/2.0/consumer": {"file": "v2.0_consumer-access_token-notexistingtoken.json", "status": 401}
}
```

## Refreshing from the live Marketplace

Local only — never run inside CI/test mode:

```
./console marketplace:record-fixtures
```

Re-record a subset:

```
./console marketplace:record-fixtures --only=plugins,plugins/SecurityInfo/info
```

Override the recording target:

```
./console marketplace:record-fixtures --domain=https://plugins.matomo.org
```

After recording, review the diff, run `tests:run --group Marketplace`, and commit.

## Per-test overrides

Tests that need a different response for a known endpoint (e.g. createAccount returning 409 instead of 200) register a temporary override:

```php
FixtureRepository::setOverride('/api/2.0/createAccount', ['file' => 'v2.0_createAccount_duplicate-email.json', 'status' => 409]);
// ... run assertion ...
FixtureRepository::clearOverrides();
```

## Adding a new fixture by hand

1. Drop the JSON into this directory with a descriptive name.
2. Add a `manifest.json` entry mapping the canonical URL key to the filename.
3. Run the affected test to confirm.
