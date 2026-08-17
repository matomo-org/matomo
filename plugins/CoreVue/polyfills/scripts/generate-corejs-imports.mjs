/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Generates plugins/CoreVue/polyfills/src/corejs-imports.generated.ts.
 *
 * The former Vue CLI / Babel toolchain compiled every plugin's Vue sources with
 * `@babel/preset-env` `useBuiltIns: 'usage'`, so each plugin bundle only carried the core-js
 * polyfills it actually used. The Vite build transpiles plugin sources with esbuild, which does
 * not inject any core-js, so the shared CoreVue polyfill has to provide them instead. Importing
 * the whole `core-js/stable` there shipped every stable polyfill our browserslist could ever
 * need regardless of whether the code used it.
 *
 * This script restores usage-based trimming at the polyfill level: it runs every Vue source
 * under plugins/<Plugin>/vue/src through `@babel/preset-env` `useBuiltIns: 'usage'` (using the
 * targets from .browserslistrc), collects the union of core-js modules Babel would inject, and
 * writes them out as explicit `import 'core-js/modules/...';` statements. The polyfill entry
 * imports that generated file instead of `core-js/stable`, so esbuild bundles only the polyfills
 * that are genuinely referenced.
 *
 * It runs automatically as part of `./console vue:build-polyfill`; the generated file is committed
 * so the bundle stays reproducible and the diff is reviewable.
 */

import { transformSync } from '@babel/core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
const polyfillsDir = path.join(scriptDir, '..');
const repoRoot = path.join(polyfillsDir, '..', '..', '..');
const pluginsDir = path.join(repoRoot, 'plugins');
const browserslistConfig = path.join(repoRoot, '.browserslistrc');
const outFile = path.join(polyfillsDir, 'src', 'corejs-imports.generated.ts');

// Keep the corejs version in lockstep with the installed package so preset-env expands usage
// against the real module set.
const corejsVersion = JSON.parse(
  fs.readFileSync(path.join(repoRoot, 'node_modules', 'core-js', 'package.json'), 'utf8'),
).version;

/**
 * Returns the names of plugins that are git submodules, parsed from .gitmodules. Used only to warn
 * when a submodule is not initialized, since git itself is not reliably usable here (the build also
 * runs inside a container where this checkout may be a git worktree).
 */
function submodulePluginNames() {
  const gitmodules = path.join(repoRoot, '.gitmodules');
  if (!fs.existsSync(gitmodules)) {
    return new Set();
  }

  const names = new Set();
  for (const match of fs.readFileSync(gitmodules, 'utf8').matchAll(/^\s*path\s*=\s*plugins\/([^/\s]+)/gm)) {
    names.add(match[1]);
  }

  return names;
}

/**
 * Recursively collects `*.{ts,js,vue}` source files under a directory, excluding `*.spec.*` test
 * files (they never ship). Plain recursion is used rather than `readdirSync({ recursive: true })`
 * so the scan does not depend on a recent Node version silently.
 */
function collectSourceFiles(dir) {
  const files = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...collectSourceFiles(full));
    } else if (/\.(ts|js|vue)$/.test(entry.name) && !/\.spec\.(ts|js)$/.test(entry.name)) {
      files.push(full);
    }
  }

  return files;
}

/**
 * Returns the source files to scan across all plugins' `vue/src` directories. An uninitialized
 * submodule (empty directory) is reported rather than silently skipped so its Vue sources are not
 * missed; the canonical list is regenerated in CI with all submodules checked out.
 */
function collectAllSourceFiles() {
  const submodules = submodulePluginNames();
  const uninitializedSubmodules = [];
  const files = [];

  for (const entry of fs.readdirSync(pluginsDir, { withFileTypes: true })) {
    if (!entry.isDirectory()) {
      continue;
    }

    const pluginDir = path.join(pluginsDir, entry.name);
    if (submodules.has(entry.name) && fs.readdirSync(pluginDir).length === 0) {
      uninitializedSubmodules.push(entry.name);
      continue;
    }

    const srcDir = path.join(pluginDir, 'vue', 'src');
    if (fs.existsSync(srcDir)) {
      files.push(...collectSourceFiles(srcDir));
    }
  }

  if (uninitializedSubmodules.length) {
    // eslint-disable-next-line no-console
    console.warn(
      `[polyfill] WARNING: skipping uninitialized submodule(s): ${uninitializedSubmodules.join(', ')}. `
      + 'Run `git submodule update --init` so their Vue sources are included; the CI drift check '
      + 'regenerates the canonical list with all submodules present.',
    );
  }

  return files.sort();
}

/**
 * Returns the concatenated contents of all `<script>` blocks in an SFC (both `<script>` and
 * `<script setup>`), or the raw source for plain .ts/.js files.
 *
 * Template expressions are intentionally not compiled and scanned: compiling every SFC template
 * and scanning the render functions was verified to add no further core-js modules on top of the
 * `<script>` scan, because template expressions here only call helpers/methods that already appear
 * in a script somewhere. Should that ever change, a template-only polyfillable call would need to
 * be mirrored in a script (or this scan extended to compile templates).
 */
function extractScript(file, source) {
  if (!file.endsWith('.vue')) {
    return source;
  }

  const scripts = [...source.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/gi)];
  return scripts.map((match) => match[1]).join('\n');
}

function collectCoreJsModules(files) {
  const modules = new Set();

  for (const file of files) {
    const code = extractScript(file, fs.readFileSync(file, 'utf8'));
    if (!code.trim()) {
      continue;
    }

    // `useBuiltIns: 'usage'` rewrites the code to `import 'core-js/modules/...'` for exactly the
    // features this file uses that the target browsers lack. We only need the injected imports, so
    // the transformed code itself is discarded. `browserslistConfigFile` points at the repo's
    // .browserslistrc explicitly so the target set never depends on the process working directory.
    const result = transformSync(code, {
      // preset-typescript keys off the extension, so present .vue scripts as .ts.
      filename: file.replace(/\.vue$/, '.ts'),
      configFile: false,
      babelrc: false,
      browserslistConfigFile: browserslistConfig,
      presets: [
        ['@babel/preset-env', {
          useBuiltIns: 'usage',
          corejs: { version: corejsVersion, proposals: false },
          modules: false,
        }],
        '@babel/preset-typescript',
      ],
    });

    const importRegex = /core-js\/modules\/([a-z0-9.-]+?)(?:\.js)?["']/g;
    let match;
    while ((match = importRegex.exec(result.code)) !== null) {
      modules.add(match[1]);
    }
  }

  return [...modules].sort();
}

function render(modules) {
  const imports = modules.map((name) => `import 'core-js/modules/${name}.js';`).join('\n');

  return `/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable */

// AUTO-GENERATED - DO NOT EDIT.
//
// Regenerated by plugins/CoreVue/polyfills/scripts/generate-corejs-imports.mjs as part of
// \`./console vue:build-polyfill\`. It lists the exact core-js modules used by the Vue sources
// under plugins/*/vue/src for the browsers in .browserslistrc, so the shared polyfill only ships
// the polyfills that are actually needed instead of the whole of core-js/stable.

${imports}
`;
}

const files = collectAllSourceFiles();
const modules = collectCoreJsModules(files);
fs.writeFileSync(outFile, render(modules));

// eslint-disable-next-line no-console
console.log(
  `[polyfill] wrote ${modules.length} core-js imports to ${path.relative(repoRoot, outFile)} `
  + `(scanned ${files.length} source files)`,
);
