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
window['tslib'] = tslib;

window.vueSanitize = DOMPurify.sanitize.bind(DOMPurify);

