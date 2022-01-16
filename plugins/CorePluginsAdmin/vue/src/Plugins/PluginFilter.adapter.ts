/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import PluginFilter from './PluginFilter';

export default function piwikPluginFilter(): IDirective {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope: IScope, element: JQuery) {
      PluginFilter.mounted(element[0]);
    },
  };
}

window.angular.module('piwikApp').directive('piwikPluginFilter', piwikPluginFilter);
