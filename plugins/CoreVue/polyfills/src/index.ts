/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable */

// this is a dummy file used to compile core-js polyfills so we don't have to include them in any
// other file.

import DOMPurify from 'dompurify';
import * as tslib from 'tslib';

window.tslib = tslib;

import './jqueryNativeEventTrigger';

// modify Vue's escaping functionality to also escape angularjs {{ fields.
// vue doesn't do this since it doesn't have this problem;
const oldToDisplayString = window.Vue.toDisplayString;
window.Vue.toDisplayString = function matomoToDisplayString(val: unknown): string {
  let result = oldToDisplayString.call(this, val);
  result = result.replace(/{{/g, '{&#8291;{');
  return result;
};

window.vueSanitize = function vueSanitize(val: unknown): string {
  let result = DOMPurify.sanitize(val);
  result = result.replace(/{{/g, '{&#8291;{');
  return result;
};
