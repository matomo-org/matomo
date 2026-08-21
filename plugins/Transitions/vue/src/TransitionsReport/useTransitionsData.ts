/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { onBeforeUnmount, ref } from 'vue';
import { Matomo } from 'CoreHome';
import { TransitionsError, TransitionsReportData } from './types';
import {
  buildPageviewsTooltip,
  buildReport,
  resolveError,
  seedGroupTitles,
} from './transitionsReportData';

/** The API method behind the report, i.e. the one whose failure is the report's own failure. */
const REPORT_API_METHOD = 'Transitions.getTransitionsForAction';

/**
 * Loads the data for one action and owns the loading/error state around it.
 *
 * The request and the parsing stay in the legacy Piwik_Transitions_Model/Ajax pair so the wire
 * contract is unchanged; this composable only drives them and hands the result to
 * transitionsReportData for shaping.
 *
 * Requests are last-request-wins: a response is dropped when a newer load has started or the
 * component has since unmounted, so rapid report or type switching cannot paint stale data.
 */
export function useTransitionsData() {
  const isLoading = ref(false);
  const error = ref<TransitionsError|null>(null);
  const report = ref<TransitionsReportData|null>(null);

  let requestId = 0;
  let disposed = false;

  onBeforeUnmount(() => {
    disposed = true;
  });

  const isCurrent = (id: number) => !disposed && id === requestId;

  function load(actionType: string, actionName: string, overrideParams: Record<string, string>) {
    requestId += 1;
    const id = requestId;

    isLoading.value = true;
    error.value = null;

    const ajax = new window.Piwik_Transitions_Ajax();
    ajax.setErrorCallback((errorName, params) => {
      // The model drives the site's total pageviews through this same instance, so the callback
      // also sees failures of that request. Only a failure of the report itself should replace
      // the report with an error; a missing total just leaves its tooltip empty.
      if (params.method !== REPORT_API_METHOD || !isCurrent(id)) {
        return;
      }

      isLoading.value = false;
      report.value = null;
      error.value = resolveError(errorName, actionName);
    });

    const model = new window.Piwik_Transitions_Model(ajax);
    seedGroupTitles(model);

    model.loadData(actionType, actionName, overrideParams, () => {
      if (!isCurrent(id)) {
        return;
      }

      isLoading.value = false;
      error.value = null;
      report.value = buildReport(model, actionType, actionName);

      // Fired once per page load in parallel with the first report, so on that report the total
      // is still in flight above. Fill the tooltip in when it lands, the way the legacy renderer's
      // tooltip callback did by being evaluated at hover time.
      model.whenTotalNbPageviewsLoaded((totalNbPageviews) => {
        if (!isCurrent(id) || !report.value) {
          return;
        }

        // Assigned in place rather than replacing the report: a new object invalidates the
        // sections and ribbon-row computeds, which re-measures and re-lays-out both ribbon
        // layers for the sake of one tooltip string.
        report.value.pageviewsTooltip = buildPageviewsTooltip(model, totalNbPageviews);
      });

      Matomo.postEvent('Transitions.dataChanged', { actionType, actionName });
    });
  }

  return {
    isLoading,
    error,
    report,
    load,
  };
}
