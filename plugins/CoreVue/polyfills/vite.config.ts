/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineConfig } from 'vite';
import { babel } from '@rollup/plugin-babel';
import path from 'node:path';

// Builds the shared CoreVue polyfill bundle. Unlike the per-plugin libraries this bundle is fully
// self-contained (no externals): it ships core-js, DOMPurify, tslib and the AbortController
// polyfill, and exposes helpers such as window.tslib / window.vueSanitize. BuildPolyfill.php runs
// this config twice, toggling minification through MATOMO_VUE_PHASE.
const isMin = process.env.MATOMO_VUE_PHASE === 'min';

export default defineConfig({
  plugins: [
    // The plugin libraries are transpiled by esbuild, but the polyfill entry is run through Babel
    // so @babel/preset-env can expand `import 'core-js/stable'` into exactly the polyfills required
    // by the browsers in .browserslistrc (useBuiltIns: 'entry'), instead of shipping all of core-js.
    babel({
      babelHelpers: 'bundled',
      extensions: ['.ts', '.js', '.mjs'],
      // Only use the inline configuration below, not any babel config file in the repo.
      configFile: false,
      babelrc: false,
      presets: [
        ['@babel/preset-env', { useBuiltIns: 'entry', corejs: '3.49' }],
        '@babel/preset-typescript',
      ],
    }),
  ],
  build: {
    outDir: path.join(__dirname, 'dist'),
    emptyOutDir: false,
    target: 'es2015',
    minify: isMin ? 'terser' : false,
    sourcemap: false,
    chunkSizeWarningLimit: 100000,
    lib: {
      entry: path.join(__dirname, 'src', 'index.ts'),
      name: 'MatomoPolyfills',
      formats: ['iife'],
      fileName: () => (isMin ? 'MatomoPolyfills.min.js' : 'MatomoPolyfills.js'),
    },
  },
});
