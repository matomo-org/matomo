/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import fs from 'node:fs';
import path from 'node:path';

// Specs import other plugins by their plain name (e.g. `from 'CoreHome'`); at runtime these resolve
// to the UMD globals. In tests they are normally replaced via vi.mock, but Vitest still needs the
// specifier to resolve, so alias each plugin name to its source entry point.
const pluginsDir = path.join(__dirname, 'plugins');
const pluginAliases = Object.fromEntries(
  fs.readdirSync(pluginsDir)
    .filter((name) => fs.existsSync(path.join(pluginsDir, name, 'vue', 'src', 'index.ts')))
    .map((name) => [name, path.join(pluginsDir, name, 'vue', 'src', 'index.ts')]),
);

// Runs the Vue component unit tests (previously executed with Jest via vue-cli). Specs live next to
// the sources in plugins/<Plugin>/vue/src and use the globals API (describe/it/expect/vi).
export default defineConfig({
  plugins: [
    vue({ template: { transformAssetUrls: false } }),
  ],
  resolve: {
    alias: pluginAliases,
    extensions: ['.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json', '.vue'],
  },
  test: {
    globals: true,
    environment: 'jsdom',
    // Some component specs mount components that fire real (unmocked) AJAX calls; in environments
    // without a backend these reject with a connection error after the test has finished. Jest
    // ignored such stray async errors; Vitest fails the run on them. Failing assertions are still
    // reported independently, so restore the previous (lenient) behaviour here.
    dangerouslyIgnoreUnhandledErrors: true,
    // Match the page URL Jest's jsdom used (http://localhost/). Vitest defaults to
    // http://localhost:3000/, which would break tests that intercept requests to localhost.
    environmentOptions: {
      jsdom: {
        url: 'http://localhost/',
      },
    },
    include: ['plugins/*/vue/**/*.spec.[tj]s', 'plugins/*/tests/Javascript/**/*.spec.[tj]s'],
    setupFiles: ['./tests/client/bootstrap.jest.js'],
    // Match the previous Jest behaviour, which did not auto-reset mocks between tests.
    clearMocks: false,
    root: path.resolve(__dirname),
  },
});
