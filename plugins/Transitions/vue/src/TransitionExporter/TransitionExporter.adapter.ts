/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective } from 'angular';
import TransitionExporter from './TransitionExporter';

function transitionExporter(): IDirective {
  return {
    restrict: 'A',
    link(scope, element) {
      TransitionExporter.mounted(element[0]);
    },
  };
}

window.angular.module('piwikApp').directive('transitionExporter', transitionExporter);
