/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Test double for the legacy Piwik_Transitions_Model/Ajax pair the renderer drives. It reproduces
 * the parts of their contract the composable relies on, so specs can drive the component without a
 * backend.
 */

export interface FakeReport {
  date?: string;
  pageviews?: number;
  loops?: number;
  exits?: number;
  directEntries?: number;
  totalNbPageviews?: number|false;
  /** Per-group totals and detail rows, keyed by group name. */
  groups?: Record<string, { total: number; details?: { label?: string; url?: string;
    referrals: number; }[] }>;
}

export interface FakeTransitionsBackend {
  /** Resolves the oldest pending load with the configured report. */
  respond(): void;
  /** Resolves the newest pending load, leaving older ones outstanding. */
  respondNewest(): void;
  /** Fails the oldest pending load with an API exception name. */
  fail(errorName: string): void;
  /**
   * Invokes the error callback for a request other than the report's own, the way a failure of the
   * parallel total-pageviews request does. Independent of the load queue, since that request is
   * fired inside loadData and fails on its own schedule.
   */
  failOtherRequest(errorName: string, method: string): void;
  /**
   * Lands the fire-once total-pageviews request that `totalNbPageviews: false` leaves in flight,
   * so a spec can see what the report looks like before and after it arrives.
   */
  resolveTotalNbPageviews(nbPageviews: number): void;
  /** How many loads have been started. */
  loadCount(): number;
  /** What each load was asked for, so a spec can pin what reaches the model. */
  loads: { actionType: string; actionName: string; overrideParams: Record<string, string>|null }[];
  /** Report to serve; may be swapped between loads. */
  report: FakeReport;
}

/**
 * Mirrors Piwik_Transitions_Model.roundPercentage: whole percent from 10% up, one decimal below.
 * Worth mirroring exactly, because the report's own rounding is only correct relative to this.
 */
function roundPercentage(share: number): number {
  return share < 0.1 ? Math.round(share * 1000) / 10 : Math.round(share * 100);
}

const ALL_GROUPS = [
  'previousPages', 'previousSiteSearches', 'searchEngines', 'socialNetworks',
  'aiAssistants', 'websites', 'campaigns',
  'followingPages', 'followingSiteSearches', 'downloads', 'outlinks',
];

/**
 * Installs fake Piwik_Transitions_Model/Ajax globals and returns a handle to drive them. Loads do
 * not resolve on their own, so a spec can interleave several and control the order they finish in.
 */
export function installFakeTransitionsBackend(report: FakeReport = {}): FakeTransitionsBackend {
  let loads = 0;
  const loadArgs: FakeTransitionsBackend['loads'] = [];
  const queue: { resolve: () => void; fail: (name: string) => void }[] = [];
  let liveAjax: { errorCallback: ((name: string, params: Record<string, unknown>) => void)|null }
    |null = null;
  const totalWaiters: ((nbPageviews: number) => void)[] = [];

  const backend: FakeTransitionsBackend = {
    report,
    loadCount: () => loads,
    loads: loadArgs,
    respond() {
      const next = queue.shift();
      if (next) {
        next.resolve();
      }
    },
    respondNewest() {
      const next = queue.pop();
      if (next) {
        next.resolve();
      }
    },
    fail(errorName: string) {
      const next = queue.shift();
      if (next) {
        next.fail(errorName);
      }
    },
    failOtherRequest(errorName: string, method: string) {
      liveAjax?.errorCallback?.(errorName, { method });
    },
    resolveTotalNbPageviews(nbPageviews: number) {
      backend.report.totalNbPageviews = nbPageviews;
      totalWaiters.splice(0).forEach((waiter) => waiter(nbPageviews));
    },
  };

  class FakeAjax {
    errorCallback: ((errorName: string, params: Record<string, unknown>) => void)|null = null;

    setErrorCallback(callback: (errorName: string, params: Record<string, unknown>) => void) {
      this.errorCallback = callback;
      liveAjax = this;
    }
  }

  class FakeModel {
    ajax: FakeAjax;

    groupTitles: Record<string, string> = {};

    date = '';

    pageviews = 0;

    loops = 0;

    exits = 0;

    directEntries = 0;

    [metric: string]: unknown;

    constructor(ajax: FakeAjax) {
      this.ajax = ajax;
    }

    private apply() {
      const data = backend.report;
      this.date = data.date ?? '2012-08-09';
      this.pageviews = data.pageviews ?? 0;
      this.loops = data.loops ?? 0;
      this.exits = data.exits ?? 0;
      this.directEntries = data.directEntries ?? 0;

      ALL_GROUPS.forEach((name) => {
        this[`${name}NbTransitions`] = data.groups?.[name]?.total ?? 0;
      });
    }

    loadData(
      actionType: string,
      actionName: string,
      overrideParams: Record<string, string>|null,
      callback: () => void,
    ) {
      loads += 1;
      loadArgs.push({ actionType, actionName, overrideParams });
      queue.push({
        resolve: () => {
          this.apply();
          callback();
        },
        fail: (name: string) => this.ajax.errorCallback?.(
          name,
          { method: 'Transitions.getTransitionsForAction' },
        ),
      });
    }

    whenTotalNbPageviewsLoaded(callback: (nbPageviews: number) => void) {
      const total = this.getTotalNbPageviews();
      if (total) {
        callback(total);
        return;
      }

      totalWaiters.push(callback);
    }

    getTotalNbPageviews() {
      return backend.report.totalNbPageviews ?? 1000;
    }

    getGroupTitle(groupName: string) {
      return this.groupTitles[groupName] ?? groupName;
    }

    getDetailsForGroup(groupName: string) {
      const details = backend.report.groups?.[groupName]?.details ?? [];
      const total = details.reduce((sum, detail) => sum + detail.referrals, 0);

      return details.map((detail) => ({
        ...detail,
        percentage: total ? roundPercentage(detail.referrals / total) : 0,
      }));
    }

    getPercentage(metric: string, formatted?: boolean) {
      const value = (this[metric] as number) || 0;
      const share = this.pageviews ? value / this.pageviews : 0;
      return formatted ? `${roundPercentage(share)}%` : share;
    }
  }

  (window as unknown as Record<string, unknown>).Piwik_Transitions_Ajax = FakeAjax;
  (window as unknown as Record<string, unknown>).Piwik_Transitions_Model = FakeModel;

  return backend;
}
