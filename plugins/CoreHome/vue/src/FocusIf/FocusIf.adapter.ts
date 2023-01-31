/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective } from 'angular';
import FocusIf from './FocusIf';

/**
 * If the given expression evaluates to true the element will be focused
 *
 * Example:
 * <input type="text" piwik-focus-if="view.editName">
 */
function piwikFocusIf(): IDirective {
  return {
    restrict: 'A',
    link: function focusIfLink(scope, element, attrs) {
      scope.$watch(attrs.piwikFocusIf, (newValue) => {
        const binding = {
          instance: null,
          value: {
            focused: newValue ? true : undefined,
            afterFocus: () => scope.$apply(),
          },
          oldValue: null,
          modifiers: {},
          dir: {},
        };

        FocusIf.updated(element[0], binding);
      });
    },
  };
}

window.angular.module('piwikApp.directive').directive('piwikFocusIf', piwikFocusIf);
