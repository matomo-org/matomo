/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import ExpandOnClick from './ExpandOnClick';

export default function piwikExpandOnClick(): IDirective {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope: IScope, element: JQuery) {
      const binding = {
        instance: null,
        value: {
          expander: element.find('.title').first()[0],
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      ExpandOnClick.mounted(element[0], binding);
      element.on('$destroy', () => ExpandOnClick.unmounted(element[0], binding));
    },
  };
}

window.angular.module('piwikApp').directive('piwikExpandOnClick', piwikExpandOnClick);
