/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective } from 'angular';
import FocusAnywhereButHere from './FocusAnywhereButHere';

/**
 * The given expression will be executed when the user presses either escape or presses something
 * outside of this element
 *
 * Example:
 * <div piwik-focus-anywhere-but-here="closeDialog()">my dialog</div>
 */
function piwikFocusAnywhereButHere(): IDirective {
  return {
    restrict: 'A',
    link: function focusAnywhereButHereLink(scope, element, attr) {
      const binding = {
        instance: null,
        value: {
          blur: () => {
            setTimeout(() => {
              scope.$apply(attr.piwikFocusAnywhereButHere);
            }, 0);
          },
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      FocusAnywhereButHere.mounted(element[0], binding);
      element.on('$destroy', () => FocusAnywhereButHere.unmounted(element[0], binding));
    },
  };
}

window.angular.module('piwikApp.directive').directive(
  'piwikFocusAnywhereButHere',
  piwikFocusAnywhereButHere,
);
