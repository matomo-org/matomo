/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IDirective, IScope, ITimeoutService } from 'angular';
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
    link: function piwikReportExportLink(scope: IScope, element: JQuery) {
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
              angular.element(document).injector().get('$rootScope').$apply();
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

angular.module('piwikApp').directive('piwikReportExport', piwikReportExport);
