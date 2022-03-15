/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import LiveWidgetRefresh from './LiveWidgetRefresh';

function piwikLiveWidgetRefresh() {
  return {
    restrict: 'A',
    scope: {
      liveRefreshAfterMs: '@',
    },
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    link(scope: any, element: JQuery) {
      LiveWidgetRefresh.mounted(element[0], {
        instance: null,
        value: {
          liveRefreshAfterMs: parseInt(scope.liveRefreshAfterMs, 10),
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      });
    },
  };
}

piwikLiveWidgetRefresh.$inject = ['piwik', '$timeout'];

window.angular.module('piwikApp').directive('piwikLiveWidgetRefresh', piwikLiveWidgetRefresh);
