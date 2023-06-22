/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import CopyToClipboard from './CopyToClipboard';

export default function matomoCopyToClipboard(): IDirective {
  return {
    restrict: 'A',
    link: function matomoCopyToClipboardLink(scope: IScope, element: JQuery) {
      const binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      CopyToClipboard.mounted(element[0], binding);
      element.on('$destroy', () => CopyToClipboard.unmounted(element[0], binding));
    },
  };
}

window.angular.module('piwikApp').directive('matomoCopyToClipboard', matomoCopyToClipboard);
