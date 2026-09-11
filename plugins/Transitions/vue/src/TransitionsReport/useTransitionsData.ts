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

/** The one request whose failure is the report's own failure. */
const REPORT_API_METHOD = 'Transitions.getTransitionsForAction';

/**
 * Loads one action and owns the loading/error state. The request and parsing stay in the legacy
 * Piwik_Transitions_Model/Ajax pair, so the wire contract is unchanged.
 *
 * Last-request-wins: a response is dropped once a newer load has started or the component has
 * unmounted, so fast switching cannot paint stale data.
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
    const model = new window.Piwik_Transitions_Model(ajax);

    ajax.setErrorCallback((errorName, params) => {
      // The site total shares this ajax instance, so its failures arrive here too. Only the
      // report's own failure should replace the report.
      if (params.method !== REPORT_API_METHOD) {
        // The total's own callback never runs on failure and the request is fired once per page,
        // so release its waiters here rather than leaving them queued for good.
        model.notifyTotalNbPageviewsLoaded(false);
        return;
      }

      if (!isCurrent(id)) {
        return;
      }

      isLoading.value = false;
      report.value = null;
      error.value = resolveError(errorName, actionName);
    });

    seedGroupTitles(model);

    model.loadData(actionType, actionName, overrideParams, () => {
      if (!isCurrent(id)) {
        return;
      }

      isLoading.value = false;
      error.value = null;
      report.value = buildReport(model, actionType, actionName);

      // Still in flight on the first report, so fill the tooltip in when it lands.
      model.whenTotalNbPageviewsLoaded((totalNbPageviews) => {
        if (!isCurrent(id) || !report.value) {
          return;
        }

        // In place, not a new object: replacing it would re-lay-out both ribbon layers for the
        // sake of one tooltip string.
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
