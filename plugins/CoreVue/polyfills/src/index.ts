/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable */

// this is a dummy file used to compile core-js polyfills so we don't have to include them in any
// other file.

// Pull in the core-js polyfills the plugin sources actually use. The previous webpack/babel
// toolchain injected these per plugin; with the esbuild based Vite build they have to be imported
// explicitly here. The import list is generated from usage across plugins/*/vue/src by
// scripts/generate-corejs-imports.mjs (run during vue:build-polyfill), so we ship only the needed
// polyfills instead of the whole of core-js/stable.
import './corejs-imports.generated';

import DOMPurify from 'dompurify';
import * as tslib from 'tslib';
import 'abortcontroller-polyfill/dist/abortcontroller-polyfill-only';

window.tslib = tslib;

// Kept as a defensive fallback for Object.fromEntries in addition to the core-js/stable import above.
Object.fromEntries = function fromEntries(it) {
  return [...it].reduce((result, [key, value]) => {
    result[key] = value;
    return result;
  }, {});
};

import './jqueryNativeEventTrigger';

function hasSafeRel(rel: string) {
  const parts = rel.split(/\s+/);
  return parts.includes('noopener') && parts.includes('noreferrer');
}

// remove target=_blank if a link doesn't have noopener noreferrer
DOMPurify.addHook('afterSanitizeAttributes', (node: Element) => {
  if (node.hasAttribute('target')
    && node.getAttribute('target') === '_blank'
    && (!node.hasAttribute('rel')
      || !hasSafeRel(node.getAttribute('rel')))
  ) {
    node.removeAttribute('target');
  }
});

window.vueSanitize = function vueSanitize(val: unknown): string {
  return DOMPurify.sanitize(val, { ADD_ATTR: ['target'] });
};

// Returns the given URL if DOMPurify considers it a valid `href` value (i.e. it uses an allowed
// scheme such as http(s)/mailto/tel and contains no dangerous payload), otherwise an empty string.
// Use it to guard dynamic `:href`/`:src` bindings, e.g. `:href="$sanitizeUrl(url)"`.
window.vueSanitizeUrl = function vueSanitizeUrl(url: string): string {
  return DOMPurify.isValidAttribute('a', 'href', url) ? url : '';
};
