/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable */

// this is a dummy file used to compile core-js polyfills so we don't have to include them in any
// other file.

// Pull in the full set of stable polyfills. The previous webpack/babel toolchain injected these
// implicitly; with the esbuild based Vite build they have to be imported explicitly.
import 'core-js/stable';

import { sanitize, sanitizeTooltip, sanitizeUrl } from './sanitize';
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

window.vueSanitize = sanitize;
window.vueSanitizeTooltip = sanitizeTooltip;
window.vueSanitizeUrl = sanitizeUrl;
