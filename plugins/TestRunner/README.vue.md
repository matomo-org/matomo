## `tests:run-vue`

Run Vue component unit tests.

### Syntax

```bash
ddev matomo:console tests:run-vue [specs...] [--plugin=<Plugin>] [--run-in-band] [--verbose]
```

### Arguments and options

- `specs`
  - Accepts one or more Vue spec paths or regex fragments.
  - Pass multiple values separated by spaces.
  - Ignored when `--plugin` is provided.
- `--plugin`
  - Accepts either `CoreHome` or `plugins/CoreHome` style values.
  - If the value does not start with `plugins/`, it is normalized to `plugins/<Name>`.
  - Exported as `MATOMO_CURRENT_PLUGIN` for the npm test process.
  - Enforces test discovery scope to `plugins/<Name>/vue/**/*.spec.[tj]s`.
  - Fails fast with a non-zero exit if the plugin path does not exist.
- `--run-in-band`
  - Forwards Jest `--runInBand` to run tests serially.
- `--verbose`
  - Uses Symfony global verbosity and also forwards Jest `--verbose` for detailed test output.
  
### Execution details

- The command runs from the Matomo root directory.
- It executes `npm test` and appends translated test path filters when needed.

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

Run serially with verbose output:

```bash
ddev matomo:console tests:run-vue --run-in-band --verbose
```

Run with multiple spec arguments:

```bash
ddev matomo:console tests:run-vue Alert.spec.ts Notification.spec.ts
```

## Troubleshooting

- No tests found:
  - Confirm the spec path is correct relative to the Matomo root, or use a broader fragment.
- Plugin scoping issues:
  - Use `--plugin=CoreHome` or `--plugin=plugins/CoreHome`.
  - If the plugin path does not exist, the command exits with an error.
- Test output and execution mode:
  - Add `--verbose` for detailed output.
  - Add `--run-in-band` to run tests serially when debugging flaky tests.

For command help:

```bash
ddev matomo:console tests:run-vue --help
```

## How to create a new Jest test

1. Create a spec file next to the component, for example:

```bash
plugins/<PluginName>/vue/src/MyComponent/MyComponent.spec.ts
```

2. Add a minimal test:

```ts
import { mount } from '@vue/test-utils';
import MyComponent from './MyComponent.vue';

describe('<PluginName>/MyComponent', () => {
  it('renders', () => {
    const wrapper = mount(MyComponent);
    expect(wrapper.exists()).toBe(true);
  });
});
```

3. Run it:

```bash
ddev matomo:console tests:run-vue MyComponent.spec.ts
or 
ddev matomo:console tests:run-vue --plugin=MyPlugin
```
