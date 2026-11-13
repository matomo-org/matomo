/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { onBeforeUnmount, ref } from 'vue';
import { Matomo } from 'CoreHome';
import { TransitionsError, TransitionsPart, TransitionsReportData } from './types';
import {
  buildPageviewsTooltip,
  buildReport,
  resolveError,
  seedGroupTitles,
} from './transitionsReportData';

/** The one request whose failure is the report's own failure. */
const REPORT_API_METHOD = 'Transitions.getTransitionsForAction';

/**
 * Requested in parallel. Until internalReferrers is in, the report is only its layout: it carries
 * the pageviews every share is relative to, so the other parts wait for it before they show.
 */
const PARTS: TransitionsPart[] = ['internalReferrers', 'followingActions', 'externalReferrers'];

/**
 * Loads one action and owns the loading/error state. The requests and parsing stay in the legacy
 * Piwik_Transitions_Model/Ajax pair.
 *
 * Last-request-wins: a response is dropped once a newer load has started or the component has
 * unmounted, so fast switching cannot paint stale data. A failing part fails the whole report,
 * and later parts of that load are dropped.
 */
export function useTransitionsData() {
  const error = ref<TransitionsError|null>(null);
  const report = ref<TransitionsReportData|null>(null);

  let requestId = 0;
  let disposed = false;
  let currentAjax: TransitionsAjax|null = null;

  // Requests of a report that is no longer shown would only hold on to connections.
  const abortCurrent = () => {
    currentAjax?.abort();
    currentAjax = null;
  };

  onBeforeUnmount(() => {
    disposed = true;
    abortCurrent();
  });

  const isCurrent = (id: number) => !disposed && id === requestId;

  function load(actionType: string, actionName: string, overrideParams: Record<string, string>) {
    requestId += 1;
    const id = requestId;

    error.value = null;

    abortCurrent();
    const ajax = new window.Piwik_Transitions_Ajax();
    currentAjax = ajax;
    const model = new window.Piwik_Transitions_Model(ajax);
    let pendingParts = [...PARTS];
    let failed = false;

    ajax.setErrorCallback((errorName, params) => {
      // The site total shares this ajax instance, so its failures arrive here too. Only the
      // report's own failure should replace the report.
      if (params.method !== REPORT_API_METHOD) {
        // The total's own callback never runs on failure and the request is fired once per page,
        // so release its waiters here rather than leaving them queued for good.
        model.notifyTotalNbPageviewsLoaded(false);
        return;
      }

      if (!isCurrent(id) || failed) {
        return;
      }

      failed = true;
      abortCurrent();
      report.value = null;
      error.value = resolveError(errorName, actionName);
    });

    seedGroupTitles(model);

    // The layout, with every number still loading, so the page is on screen straight away.
    report.value = buildReport(model, actionType, actionName, pendingParts);

    const onPartLoaded = (part: TransitionsPart) => {
      if (!isCurrent(id) || failed) {
        return;
      }

      pendingParts = pendingParts.filter((pending) => pending !== part);

      if (pendingParts.includes('internalReferrers')) {
        return;
      }

      report.value = buildReport(model, actionType, actionName, pendingParts);

      if (part === 'internalReferrers') {
        // The first report with data. The site total may still be in flight, so fill the tooltip
        // in when it lands; a report rebuilt by a later part picks it up from the model itself.
        model.whenTotalNbPageviewsLoaded((totalNbPageviews) => {
          if (!isCurrent(id) || !report.value) {
            return;
          }

          // In place, not a new object: replacing it would re-lay-out both ribbon layers for the
          // sake of one tooltip string.
          report.value.pageviewsTooltip = buildPageviewsTooltip(model, totalNbPageviews);
        });

        // The action is on screen now, so its export can be offered.
        Matomo.postEvent('Transitions.dataChanged', { actionType, actionName });
      }
    };

    PARTS.forEach((part) => {
      model.loadPart(part, actionType, actionName, overrideParams, () => onPartLoaded(part));
    });
  }

  return {
    error,
    report,
    load,
  };
}
