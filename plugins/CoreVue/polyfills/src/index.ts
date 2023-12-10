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
