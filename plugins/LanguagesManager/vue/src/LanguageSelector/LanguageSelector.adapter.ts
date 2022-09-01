/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IScope } from 'angular';
import LanguageSelector from './LanguageSelector';

function languageSelection() {
  return {
    restrict: 'C',
    link: function languageSelectionLink(scope: IScope, element: JQuery) {
      const binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      LanguageSelector.mounted(element[0], binding);
      element.on('$destroy', () => {
        LanguageSelector.unmounted(element[0], binding);
      });
    },
  };
}

window.angular.module('piwikApp').directive('languageSelection', languageSelection);
