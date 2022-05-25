/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { translate } from '../translate';
import ReportExportPopover from './ReportExportPopover.vue';
import Matomo from '../Matomo/Matomo';
import createVueApp from '../createVueApp';

interface ReportExportArgs {
  reportTitle: string;
  requestParams: QueryParameters;
  reportFormats: Record<string, unknown>;
  apiMethod: string;
  maxFilterLimit: number;
  onClose?: () => void;
}

const { $ } = window;

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<ReportExportArgs>): void {
    el.addEventListener('click', () => {
      const popoverParamBackup = MatomoUrl.hashParsed.value.popover;

      const dataTable = $(el).closest('[data-report]').data('uiControlObject');
      const popover = window.Piwik_Popover.showLoading('Export');

      const formats = binding.value.reportFormats;

      let reportLimit = dataTable.param.filter_limit;
      if (binding.value.maxFilterLimit > 0) {
        reportLimit = Math.min(reportLimit, binding.value.maxFilterLimit);
      }

      const optionFlat = dataTable.param.flat === true
        || dataTable.param.flat === 1
        || dataTable.param.flat === '1';

      const props = {
        initialReportType: 'default',
        initialReportLimit: reportLimit > 0 ? reportLimit : 100,
        initialReportLimitAll: reportLimit === -1 ? 'yes' : 'no',
        initialOptionFlat: optionFlat,
        initialOptionExpanded: true,
        initialOptionFormatMetrics: false,
        hasSubtables: optionFlat || dataTable.numberOfSubtables > 0,
        availableReportFormats: {
          default: formats,
          processed: {
            XML: formats.XML,
            JSON: formats.JSON,
          },
        },
        availableReportTypes: {
          default: translate('CoreHome_StandardReport'),
          processed: translate('CoreHome_ReportWithMetadata'),
        },
        limitAllOptions: {
          yes: translate('General_All'),
          no: translate('CoreHome_CustomLimit'),
        },
        maxFilterLimit: binding.value.maxFilterLimit,
        dataTable,
        requestParams: binding.value.requestParams,
        apiMethod: binding.value.apiMethod,
      };

      const app = createVueApp({
        template: `
          <popover v-bind="bind"/>`,
        data() {
          return {
            bind: props,
          };
        },
      });
      app.component('popover', ReportExportPopover);

      const mountPoint = document.createElement('div');
      app.mount(mountPoint);

      const { reportTitle } = binding.value;
      window.Piwik_Popover.setTitle(
        `${translate('General_Export')} ${Matomo.helper.htmlEntities(reportTitle)}`,
      );
      window.Piwik_Popover.setContent(mountPoint);

      window.Piwik_Popover.onClose(() => {
        app.unmount();

        if (popoverParamBackup !== '') {
          setTimeout(() => {
            MatomoUrl.updateHash({
              ...MatomoUrl.hashParsed.value,
              popover: popoverParamBackup,
            });

            if (binding.value.onClose) {
              binding.value.onClose();
            }
          }, 100);
        }
      });

      setTimeout(() => {
        popover.dialog();

        $('.exportFullUrl, .btn', popover).tooltip({
          track: true,
          show: false,
          hide: false,
        });
      }, 100);
    });
  },
};
