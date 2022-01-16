/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import SelectOnFocus from './SelectOnFocus';

export default function piwikSelectOnFocus(): IDirective {
  return {
    restrict: 'A',
    link: function piwikSelectOnFocusLink(scope: IScope, element: JQuery) {
      const binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      SelectOnFocus.mounted(element[0], binding);
      element.on('$destroy', () => SelectOnFocus.unmounted(element[0], binding));
    },
  };
}

window.angular.module('piwikApp').directive('piwikSelectOnFocus', piwikSelectOnFocus);
