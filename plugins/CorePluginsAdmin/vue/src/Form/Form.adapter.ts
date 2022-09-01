/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import Form from './Form';

export default function piwikForm(): IDirective {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope: IScope, element: JQuery) {
      Form.mounted(element[0]);
    },
  };
}

window.angular.module('piwikApp').directive('piwikForm', piwikForm);
