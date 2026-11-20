/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { translate } from '../translate';
import ReportExportPopover from './ReportExportPopover.vue';
import Matomo from '../Matomo/Matomo';
import createVueApp from '../createVueApp';
import findReportRoot from '../DataTable/reportScope';

export interface ReportExportArgs {
  reportTitle: string;
  requestParams: QueryParameters;
  reportFormats: Record<string, unknown>;
  apiMethod: string;
  maxFilterLimit: number;
  canExportFlat?: boolean;
  onClose?: () => void;
}

const { $ } = window;

// The args each icon was last rendered with. Vue hands a directive a new binding on every render,
// so a click handler reading the one it was mounted with would export the report as it was then.
const latestArgs = new WeakMap<HTMLElement, ReportExportArgs>();

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<ReportExportArgs>): void {
    latestArgs.set(el, binding.value);

    el.addEventListener('click', () => {
      const args = latestArgs.get(el);
      if (!args) {
        return;
      }

      const popoverParamBackup = MatomoUrl.hashParsed.value.popover;

      // `data-report` sits on `.dataTable`, so this must resolve through the report scope: the
      // export icon is moving up into the header, which is rendered outside the table.
      const dataTable = findReportRoot(el).data('uiControlObject');
      if (!dataTable) {
        // The report this icon belongs to has no table yet, so there is nothing to describe. Bail
        // before opening the popover: it has no close button while loading, so a throw past this
        // point strands it on screen.
        return;
      }

      const popover = window.Piwik_Popover.showLoading('Export');

      const formats = args.reportFormats;

      let reportLimit = dataTable.param.filter_limit;
      if (args.maxFilterLimit > 0) {
        reportLimit = Math.min(reportLimit, args.maxFilterLimit);
      }

      const isDataTableFlat = dataTable.param.flat === true
        || dataTable.param.flat === 1
        || dataTable.param.flat === '1';

      const optionShowDimensions = dataTable.param.show_dimensions === true
        || dataTable.param.show_dimensions === 1
        || dataTable.param.show_dimensions === '1';
      const hasSubtables = isDataTableFlat || dataTable.numberOfSubtables > 0;
      const canExportFlat = args.canExportFlat ?? hasSubtables;
      // Intentional product behaviour:
      // when flat export is available, open the popover with TSV + flat selected.
      const defaultFlatOnOpen = canExportFlat;
      const defaultExpandedOnOpen = false;

      const props = {
        initialReportType: 'default',
        initialReportFormat: 'TSV',
        initialReportLimit: reportLimit > 0 ? reportLimit : 100,
        initialReportLimitAll: reportLimit === -1 ? 'yes' : 'no',
        initialOptionFlat: defaultFlatOnOpen,
        initialOptionShowDimensions: optionShowDimensions,
        initialOptionExpanded: defaultExpandedOnOpen,
        initialOptionFormatMetrics: false,
        hasSubtables,
        canExportFlat,
        availableReportFormats: {
          default: formats,
          processed: {
            JSON: formats.JSON,
            XML: formats.XML,
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
        maxFilterLimit: args.maxFilterLimit,
        dataTable,
        requestParams: args.requestParams,
        apiMethod: args.apiMethod,
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

      const { reportTitle } = args;
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

            if (args.onClose) {
              args.onClose();
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
  updated(el: HTMLElement, binding: DirectiveBinding<ReportExportArgs>): void {
    latestArgs.set(el, binding.value);
  },
  unmounted(el: HTMLElement): void {
    latestArgs.delete(el);
  },
};
