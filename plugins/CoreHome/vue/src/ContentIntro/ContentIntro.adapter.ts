/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import ContentIntro from './ContentIntro';

export default function piwikContentIntro(): IDirective {
  return {
    restrict: 'A',
    link: function piwikContentIntroLink(scope: IScope, element: JQuery) {
      ContentIntro.mounted(element[0]);
    },
  };
}

window.angular.module('piwikApp').directive('piwikContentIntro', piwikContentIntro);
