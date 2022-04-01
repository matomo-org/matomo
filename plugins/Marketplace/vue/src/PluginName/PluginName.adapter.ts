/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAttributes, IScope } from 'angular';
import PluginName from './PluginName';

function piwikPluginName() {
  return {
    restrict: 'A',
    link(scope: IScope, element: JQuery, attrs: IAttributes) {
      const binding = {
        instance: null,
        value: {
          pluginName: attrs.piwikPluginName,
          activePluginTab: attrs.activeplugintab,
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      PluginName.mounted(element[0], binding);
      element.on('$destroy', () => {
        PluginName.unmounted(element[0], binding);
      });
    },
  };
}

window.angular.module('piwikApp').directive('piwikPluginName', piwikPluginName);
