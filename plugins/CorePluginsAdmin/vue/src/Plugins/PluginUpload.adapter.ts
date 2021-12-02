/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope } from 'angular';
import PluginUpload from './PluginUpload';

export default function piwikPluginUpload(): IDirective {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope: IScope, element: JQuery) {
      PluginUpload.mounted();
    },
  };
}

piwikPluginUpload.$inject = [];

angular.module('piwikApp').directive('piwikPluginUpload', piwikPluginUpload);
