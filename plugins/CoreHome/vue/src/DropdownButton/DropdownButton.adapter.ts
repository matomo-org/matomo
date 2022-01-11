/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import DropdownButton from './DropdownButton';

export default function piwikDropdownButton(): IDirective {
  return {
    restrict: 'C',
    link: function piwikDropdownButtonLink(scope: IScope, element: JQuery) {
      DropdownButton.mounted(element[0]);
    },
  };
}

window.angular.module('piwikApp').directive('dropdownButton', piwikDropdownButton);
