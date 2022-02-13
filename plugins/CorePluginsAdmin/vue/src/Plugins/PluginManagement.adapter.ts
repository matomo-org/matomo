/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import PluginManagement from './PluginManagement';

export default function piwikPluginManagement(): IDirective {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope: IScope, element: JQuery) {
      const binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      PluginManagement.mounted(element[0], binding);
    },
  };
}

window.angular.module('piwikApp').directive('piwikPluginManagement', piwikPluginManagement);
