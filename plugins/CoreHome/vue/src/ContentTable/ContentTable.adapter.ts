/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import ContentTable from './ContentTable';

export default function piwikContentTable(): IDirective {
  return {
    restrict: 'A',
    link: function piwikContentTableLink(scope: IScope, element: JQuery) {
      ContentTable.mounted(element[0]);
    },
  };
}

window.angular.module('piwikApp').directive('piwikContentTable', piwikContentTable);
