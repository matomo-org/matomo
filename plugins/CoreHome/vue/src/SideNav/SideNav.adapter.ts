/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, ITimeoutService } from 'angular';
import SideNav from './SideNav';

export default function piwikSideNav($timeout: ITimeoutService): IDirective {
  return {
    restrict: 'A',
    priority: 10,
    link: function linkPiwikSideNav(scope, element, attr) {
      const binding = {
        instance: null,
        value: {
          activator: $(attr.piwikSideNav)[0],
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      $timeout(() => {
        SideNav.mounted(element[0], binding);
      });
    },
  };
}

piwikSideNav.$inject = ['$timeout'];

window.angular.module('piwikApp.directive').directive('piwikSideNav', piwikSideNav);
