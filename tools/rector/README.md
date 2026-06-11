# Automated vendor downgrade to PHP 7.2

Matomo supports PHP >= 7.2.5, but many composer dependencies dropped PHP 7.2
support in their newer versions. To still be able to update them, composer
resolves dependencies against a newer PHP version (`config.platform.php` in
`composer.json`), and the installed vendor code is automatically transformed
back to PHP 7.2 compatible syntax using [Rector](https://getrector.com)
downgrade rules.

## How it works

1. `composer install`/`update` installs the dependency versions resolved
   against `config.platform.php`.
2. The composer `post-install-cmd`/`post-update-cmd` hooks run
   `.github/scripts/vendor-downgrade/downgrade-vendor.php`, which:
   - derives the packages needing a downgrade from
     `vendor/composer/installed.json` (every package whose declared PHP
     requirement is not satisfiable by PHP 7.2.9, see
     `compute-downgrade-paths.php` — overrides possible for packages with
     missing or wrong declarations),
   - locates a PHP >= 7.4 CLI binary (Rector cannot run on PHP 7.2; the script
     itself can) and installs the Rector version pinned in this directory,
   - runs Rector over the affected packages in multiple passes (see
     `rector-downgrade.php` in the repository root; custom rules for constructs
     Rector misses live in `tools/rector/rules/`),
   - writes a stamp file `vendor/.rector-downgraded` so repeated runs are
     no-ops until composer.lock, the Rector config or a package changes.

Because the downgrade runs on every machine that installs dependencies, it
covers local development, CI, the weekly automated composer update and the
release build (the tooling itself is removed from release packages by
`clean-build.sh`).

Runtime pieces that the syntax downgrade cannot cover:

- Functions and classes introduced after PHP 7.2 are provided by
  `symfony/polyfill-php8x` packages and `core/Polyfill/` (e.g. `WeakMap`).
- Twig generates PHP code for compiled templates at runtime; that generated
  code is made PHP 7.2 compatible by `Piwik\Twig\Php72CompatibleEnvironment`.

## Manual usage

```bash
# run the downgrade explicitly (normally happens automatically on install)
composer downgrade-vendor                # or: ddev composer downgrade-vendor

# list which installed packages need a downgrade
php .github/scripts/vendor-downgrade/compute-downgrade-paths.php

# verify all vendor code parses on PHP 7.2 (used by CI)
PHP_BIN=php7.2 .github/scripts/vendor-downgrade/lint-vendor-php72.sh

# skip the downgrade (the vendor code may then not run on PHP < 8!)
MATOMO_SKIP_VENDOR_DOWNGRADE=1 composer install
```

In the ddev environment the default PHP version stays at 7.2, so the
application always runs on the minimum supported version locally; the
downgrade transparently uses one of the newer PHP binaries available in the
web container.

## CI safety nets

- `.github/workflows/vendor-downgrade.yml` installs dependencies and lints the
  whole downgraded vendor directory with PHP 7.2 whenever composer or
  downgrade tooling files change.
- The weekly `composer-update.yml` workflow runs the same lint and includes a
  downgrade report in the PR body, so an update that cannot be downgraded
  fails before it is merged.
- The regular PHP 7.2 test jobs run against the downgraded vendor code.

## Limitations — read before upgrading a dependency

The mechanism makes upgrades to PHP 8-only versions *possible*, not
*guaranteed*. Before bumping a dependency past PHP 7.2 support, evaluate:

- **Runtime attribute reflection**: attributes are downgraded to doc comments.
  Libraries whose *behavior* depends on reading attributes at runtime (e.g.
  Symfony `AsCommand`, twig `AsTwigFilter`) lose that behavior.
- **Enums**: enums become constant-list classes, and enum-typed declarations
  and `->value` fetches inside the package are adjusted. Exotic usages
  (`::cases()`, `::from()`, `->name`, enums in `instanceof`, serialized enum
  cases, enums passed across package boundaries) are not covered — grep the
  package before upgrading.
- **Runtime code generation**: Rector only sees code on disk. Packages that
  `eval()` or generate PHP at runtime (like twig's template compiler) need
  their generated output handled separately.
- **New extension requirements and non-polyfillable symbols** (e.g. fibers)
  are hard blockers.
- A green PHP 7.2 CI suite is the final arbiter for every upgrade.

## Checklist for upgrading a PHP 8-only dependency

1. Raise the version constraint in `composer.json`. If the package needs a PHP
   version above `config.platform.php`, that cap (currently 8.1) must be
   raised consciously — a higher cap means more syntax to downgrade.
2. `ddev composer update <package>` — the downgrade runs automatically and
   fails loudly if Rector cannot process the package.
3. `PHP_BIN=php7.2 .github/scripts/vendor-downgrade/lint-vendor-php72.sh`
4. Grep the downgraded package for runtime hazards (see limitations above),
   and force-load its classes on PHP 7.2 to catch signature incompatibilities.
5. Handle the package's own API breaking changes in Matomo code as usual.
6. Run the test suites on PHP 7.2, including UI tests for user-facing areas.
