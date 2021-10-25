/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ExpandOnHover from './ExpandOnHover';

function piwikExpandOnHover() {
  return {
    restrict: 'A',
    link: function expandOnHoverLink(scope, element) {
      const binding = {
        instance: null,
        value: {
          expander: element.find('.title').first()[0],
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      const wrapped = ExpandOnHover();
      wrapped.mounted(element[0], binding, null, null);
      scope.$on('$destroy', () => wrapped.unmounted(element[0], binding, null, null));
    },
  };
}

piwikExpandOnHover.$inject = [];

angular.module('piwikApp').directive('piwikExpandOnHover', piwikExpandOnHover);
