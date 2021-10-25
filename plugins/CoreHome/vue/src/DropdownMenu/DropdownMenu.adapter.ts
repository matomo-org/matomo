/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective } from 'angular';
import DropdownMenu from './DropdownMenu';

function piwikDropdownMenu($timeout): IDirective {
  return {
    restrict: 'A',
    link: function piwikDropdownMenuLink(scope, element, attrs) {
      const binding = {
        instance: null,
        value: {
          activates: $(`#${attrs.activates}`)[0],
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      $timeout(() => {
        DropdownMenu.mounted(element[0], binding);
      });
    },
  };
}

piwikDropdownMenu.$inject = ['$timeout'];

angular.module('piwikApp').directive('piwikDropdownMenu', piwikDropdownMenu);
