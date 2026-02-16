## `tests:run-vue`

Run Vue component unit tests.

### Syntax

```bash
ddev matomo:console tests:run-vue [specs...] [--plugin=<Plugin>] [-o|--options="<extra>"]
```

### Arguments and options

- `specs`
  - Accepts one or more Vue spec paths or regex fragments.
  - Multiple values are combined into one `--testPathPattern` value joined by `|`.
- `--plugin`
  - Accepts either `CoreHome` or `plugins/CoreHome` style values.
  - If the value does not start with `plugins/`, it is normalized to `plugins/<Name>`.
  - Exported as `MATOMO_CURRENT_PLUGIN` for the npm test process.
- `-o`, `--options`
  - Forwards additional options to `vue-cli-service test:unit` through `npm test -- ...`.

### Execution details

- The command runs from the Matomo root directory.
- It executes `npm test` and appends translated CLI options.

### Examples

Run all Vue tests:

```bash
ddev matomo:console tests:run-vue
```

Run one spec by full path:

```bash
ddev matomo:console tests:run-vue plugins/CoreHome/vue/src/Alert/Alert.spec.ts
```

Run one spec by name fragment:

```bash
ddev matomo:console tests:run-vue Alert.spec.ts
```

Run tests for a specific plugin:

```bash
ddev matomo:console tests:run-vue --plugin=CoreHome
```

Run with additional forwarded options:

```bash
ddev matomo:console tests:run-vue --options="--runInBand --watch=false"
```

Run with multiple spec arguments (combined via ` ` (space)):

```bash
ddev matomo:console tests:run-vue Alert.spec.ts Notification.spec.ts
```

## Troubleshooting

- No tests found:
  - Confirm the spec path is correct relative to the Matomo root, or use a broader fragment.
  - If passing multiple spec values, remember they are joined as a regex OR pattern (`|`).
- Plugin scoping issues:
  - Use `--plugin=CoreHome` or `--plugin=plugins/CoreHome`.
  - If you see unexpected scope behavior, verify plugin name spelling and casing.
- Forwarded options and shell quoting:
  - Wrap `--options` values in quotes to avoid shell splitting issues.
  - Example: `--options="--runInBand --watch=false"`.

For command help:

```bash
ddev matomo:console tests:run-vue --help
```
