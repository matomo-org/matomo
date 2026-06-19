/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineConfig, type Plugin } from 'vite';
import vue from '@vitejs/plugin-vue';
import dts from 'vite-plugin-dts';
import fs from 'node:fs';
import path from 'node:path';

// Each plugin's Vue library is built individually. Build.php loops over the plugins to build and
// invokes scripts/vite-runner.js once per plugin, setting these environment variables:
//   MATOMO_CURRENT_PLUGIN  relative path of the plugin being built, e.g. "plugins/CoreHome"
//   MATOMO_ALL_PLUGINS     comma separated list of all plugin names that have a Vue library
//   MATOMO_VUE_PHASE       which artifact this invocation produces (see below)
//
// The phase controls the emitted file name and whether declarations are generated:
//   "prod"  -> <Plugin>.umd.js          (+ TypeScript declarations into @types/<Plugin>)
//   "min"   -> <Plugin>.umd.min.js      (minified, no declarations)
//   "dev"   -> <Plugin>.development.umd.js (watch mode, unminified, no declarations)
const rootDir = __dirname;
const pluginPath = process.env.MATOMO_CURRENT_PLUGIN || '';
const pluginName = path.basename(pluginPath);
const phase = process.env.MATOMO_VUE_PHASE || 'prod';
const isDev = phase === 'dev';
const isMin = phase === 'min';

if (!pluginPath) {
  throw new Error('The MATOMO_CURRENT_PLUGIN environment variable is not set!');
}

// Every plugin Vue library is referenced from other plugins by its plain name (e.g. `from
// 'CoreHome'`) and resolved at runtime against the global of the same name. Together with `vue`
// (global `Vue`) and `tslib` (global `tslib`, provided by the CoreVue polyfill) these are kept
// external so they are not bundled into every plugin.
function getAllPluginNames(): string[] {
  if (process.env.MATOMO_ALL_PLUGINS) {
    return process.env.MATOMO_ALL_PLUGINS.split(',').filter(Boolean);
  }

  // Fallback for direct `vite build` invocations without Build.php: scan for vue/src folders.
  const pluginsDir = path.join(rootDir, 'plugins');
  return fs.readdirSync(pluginsDir).filter(
    (name) => fs.existsSync(path.join(pluginsDir, name, 'vue', 'src')),
  );
}

const externalGlobals: Record<string, string> = { vue: 'Vue', tslib: 'tslib' };
for (const name of getAllPluginNames()) {
  if (name !== pluginName) {
    externalGlobals[name] = name;
  }
}

const distDir = path.join(rootDir, pluginPath, 'vue', 'dist');

// Tracks which other plugins this plugin actually imports, recorded as the externals are resolved
// (mirroring the externals callback of the previous webpack config). PluginUmdAssetFetcher uses
// `dependsOn` to load plugin bundles in the correct order.
const dependsOn = new Set<string>();

// `vue` and `tslib` are always external (provided as the Vue/tslib runtime globals); every other
// plugin Vue library is external too and referenced by the global of the same name.
function isExternal(source: string): boolean {
  if (source === 'vue' || source === 'tslib') {
    return true;
  }
  if (source !== pluginName && Object.prototype.hasOwnProperty.call(externalGlobals, source)) {
    dependsOn.add(source);
    return true;
  }
  return false;
}

// Writes umd.metadata.json once the bundle is generated.
function umdMetadataPlugin(): Plugin {
  return {
    name: 'matomo-umd-metadata',
    writeBundle() {
      if (isMin) {
        // The minified pass reuses the dependencies detected by the prod pass; don't overwrite.
        return;
      }
      fs.mkdirSync(distDir, { recursive: true });
      fs.writeFileSync(
        path.join(distDir, 'umd.metadata.json'),
        `${JSON.stringify({ dependsOn: [...dependsOn] }, null, 2)}\n`,
      );
    },
  };
}

export default defineConfig({
  plugins: [
    // Matomo templates reference images by their runtime URL (e.g. "plugins/CoreHome/images/..").
    // These are served by Matomo and must not be resolved/bundled as build assets, matching the
    // previous vue-loader behaviour which left such bare paths untouched.
    vue({ template: { transformAssetUrls: false } }),
    umdMetadataPlugin(),
    // Declarations are only needed for the canonical prod build; skip them for the minified pass
    // and during watch mode (matching the previous behaviour of only emitting types when
    // NODE_ENV !== 'development').
    ...(phase === 'prod'
      ? [dts({
        outDir: path.join(rootDir, '@types', pluginName),
        entryRoot: path.join(rootDir, pluginPath, 'vue', 'src'),
        include: [path.join(rootDir, pluginPath, 'vue', 'src', '**', '*')],
        tsconfigPath: path.join(rootDir, 'tsconfig.json'),
        // Resolve cross-plugin type imports (e.g. `from 'CoreHome'`) against previously generated
        // declarations in @types, exactly as the old ts-loader override did.
        compilerOptions: {
          declaration: true,
          paths: {
            '*': ['@types/*', 'node_modules/*', '*'],
          },
        },
      })]
      : []),
  ],
  resolve: {
    // The source imports single-file components without the .vue extension (e.g.
    // `from '../MatomoLoader/MatomoLoader'`), which webpack resolved automatically.
    extensions: ['.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json', '.vue'],
  },
  build: {
    outDir: distDir,
    // Never wipe dist between the prod and min passes; Build.php handles stale-file cleanup.
    emptyOutDir: false,
    target: 'es2015',
    // Keep the single scoped SFC style (ExampleVue) emitted as <Plugin>.css, as before.
    cssCodeSplit: false,
    minify: isMin ? 'terser' : false,
    sourcemap: false,
    // These bundles are loaded as plain <script> tags; bundle-size warnings are not relevant.
    chunkSizeWarningLimit: 100000,
    lib: {
      entry: path.join(rootDir, pluginPath, 'vue', 'src', 'index.ts'),
      name: pluginName,
      formats: ['umd'],
      cssFileName: pluginName,
      fileName: () => {
        if (isDev) {
          return `${pluginName}.development.umd.js`;
        }
        return isMin ? `${pluginName}.umd.min.js` : `${pluginName}.umd.js`;
      },
    },
    rollupOptions: {
      external: isExternal,
      output: {
        globals: externalGlobals,
        // Match the previous output: keep everything in a single UMD file (no shared chunks).
        inlineDynamicImports: true,
      },
    },
  },
});
