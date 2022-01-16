/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective } from 'angular';
import PluginUpload from './PluginUpload';

export default function piwikPluginUpload(): IDirective {
  return {
    restrict: 'A',
    link: function expandOnClickLink() {
      PluginUpload.mounted();
    },
  };
}

window.angular.module('piwikApp').directive('piwikPluginUpload', piwikPluginUpload);
