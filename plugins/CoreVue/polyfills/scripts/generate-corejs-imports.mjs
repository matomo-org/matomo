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
 * need (~250 KB) regardless of whether the code used it.
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
const outFile = path.join(polyfillsDir, 'src', 'corejs-imports.generated.ts');

// Keep this in sync with the corejs version declared in package.json / installed in node_modules.
const corejsVersion = JSON.parse(
  fs.readFileSync(path.join(repoRoot, 'node_modules', 'core-js', 'package.json'), 'utf8'),
).version;

/**
 * Collects every `plugins/<Plugin>/vue/src/**\/*.{ts,js,vue}` file. `.vue` files are handled by
 * extracting their `<script>` blocks, which is where any polyfillable JS lives.
 */
function collectSourceFiles() {
  const files = [];
  for (const plugin of fs.readdirSync(pluginsDir, { withFileTypes: true })) {
    if (!plugin.isDirectory()) {
      continue;
    }

    const srcDir = path.join(pluginsDir, plugin.name, 'vue', 'src');
    if (!fs.existsSync(srcDir)) {
      continue;
    }

    for (const entry of fs.readdirSync(srcDir, { recursive: true, withFileTypes: true })) {
      if (!entry.isFile()) {
        continue;
      }

      if (/\.(ts|js|vue)$/.test(entry.name)) {
        files.push(path.join(entry.parentPath ?? entry.path, entry.name));
      }
    }
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

    // Let preset-env read the targets from .browserslistrc (repoRoot is the cwd during the build);
    // `useBuiltIns: 'usage'` then rewrites the code to `import 'core-js/modules/...'` for exactly
    // the features this file uses that the target browsers lack. We only need the injected imports,
    // so the transformed code itself is discarded.
    const result = transformSync(code, {
      // preset-typescript keys off the extension, so present .vue scripts as .ts.
      filename: file.replace(/\.vue$/, '.ts'),
      configFile: false,
      babelrc: false,
      browserslistConfigFile: true,
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

const files = collectSourceFiles();
const modules = collectCoreJsModules(files);
fs.writeFileSync(outFile, render(modules));

// eslint-disable-next-line no-console
console.log(
  `[polyfill] wrote ${modules.length} core-js imports to ${path.relative(repoRoot, outFile)} `
  + `(scanned ${files.length} source files)`,
);
