/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective } from 'angular';
import Dashboard from './Dashboard';
import { DashboardLayout } from '../types';

export default function piwikDashboard(): IDirective {
  return {
    restrict: 'A',
    scope: {
      dashboardid: '=',
      layout: '=',
    },
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    link: function expandOnClickLink(scope: any, element: JQuery) {
      const binding = {
        instance: null,
        value: {
          idDashboard: scope.dashboardid as string,
          layout: scope.layout as DashboardLayout,
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      Dashboard.mounted(element[0], binding);

      // using scope destroy instead of element destroy event, since piwik-dashboard elements
      // are removed manually, outside of angularjs/vue workflow, so element destroy is not
      // triggered
      scope.$on('$destroy', () => {
        Dashboard.unmounted();
      });
    },
  };
}

window.angular.module('piwikApp').directive('piwikDashboard', piwikDashboard);
