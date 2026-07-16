/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/*
 * Builds the Vue library of a single plugin with Vite. Invoked once per plugin by
 * CoreVue/Commands/Build.php, which sets MATOMO_CURRENT_PLUGIN and MATOMO_ALL_PLUGINS.
 *
 * Usage:
 *   node vite-runner.mjs <plugins/Name>            build <Name>.umd.js + <Name>.umd.min.js
 *   node vite-runner.mjs <plugins/Name> --watch    watch and rebuild <Name>.development.umd.js
 *
 * A normal build runs two passes (unminified, then minified) because Vite emits a single artifact
 * per invocation. The phase is communicated to vite.config.ts through MATOMO_VUE_PHASE.
 */

import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { build } from 'vite';

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
// plugins/CoreVue/scripts -> repository root
const rootDir = path.resolve(scriptDir, '..', '..', '..');
const configFile = path.join(rootDir, 'vite.config.ts');

const args = process.argv.slice(2);
const watch = args.includes('--watch');
const pluginPath = args.find((arg) => !arg.startsWith('--')) || process.env.MATOMO_CURRENT_PLUGIN;

if (!pluginPath) {
  console.error('vite-runner: no plugin path given (argument or MATOMO_CURRENT_PLUGIN).');
  process.exit(1);
}

process.env.MATOMO_CURRENT_PLUGIN = pluginPath;

async function runPhase(phase, overrides = {}) {
  process.env.MATOMO_VUE_PHASE = phase;
  await build({ configFile, root: rootDir, ...overrides });
}

try {
  if (watch) {
    await runPhase('dev', { build: { watch: {} } });
  } else {
    await runPhase('prod');
    await runPhase('min');
  }
} catch (error) {
  console.error(error);
  process.exit(1);
}
