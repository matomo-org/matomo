/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, ITimeoutService } from 'angular';
import ReportExport from './ReportExport';

export default function piwikReportExport($timeout: ITimeoutService): IDirective {
  return {
    restrict: 'A',
    scope: {
      reportTitle: '@',
      requestParams: '@',
      reportFormats: '@',
      apiMethod: '@',
      maxFilterLimit: '@',
    },
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    link: function piwikReportExportLink(scope: any, element: JQuery) {
      const binding = {
        instance: null,
        value: {
          reportTitle: scope.reportTitle,
          requestParams: scope.requestParams,
          reportFormats: typeof scope.reportFormats === 'string'
            ? JSON.parse(scope.reportFormats)
            : scope.reportFormats,
          apiMethod: scope.apiMethod,
          maxFilterLimit: parseInt(scope.maxFilterLimit, 10),
          onClose: () => {
            $timeout(() => {
              window.angular.element(document).injector().get('$rootScope').$apply();
            }, 10);
          },
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      ReportExport.mounted(element[0], binding);
    },
  };
}

piwikReportExport.$inject = ['$timeout'];

window.angular.module('piwikApp').directive('piwikReportExport', piwikReportExport);
