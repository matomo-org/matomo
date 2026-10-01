/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable */

// this is a dummy file used to compile core-js polyfills so we don't have to include them in any
// other file.

import { sanitize, sanitizeTooltip, sanitizeUrl } from './sanitize';
import * as tslib from 'tslib';
import 'abortcontroller-polyfill/dist/abortcontroller-polyfill-only';

window.tslib = tslib;

// fromEntries does not have a polyfill in @vue/cli-plugin-babel/preset
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
