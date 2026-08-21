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
  /** Fails the pending load with an API exception name. */
  fail(errorName: string): void;
  /** How many loads have been started. */
  loadCount(): number;
  /** Report to serve; may be swapped between loads. */
  report: FakeReport;
  pending(): number;
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
  const queue: { resolve: () => void; fail: (name: string) => void }[] = [];

  const backend: FakeTransitionsBackend = {
    report,
    loadCount: () => loads,
    pending: () => queue.length,
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
  };

  class FakeAjax {
    errorCallback: ((errorName: string) => void)|null = null;

    setErrorCallback(callback: (errorName: string) => void) {
      this.errorCallback = callback;
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
      queue.push({
        resolve: () => {
          this.apply();
          callback();
        },
        fail: (name: string) => this.ajax.errorCallback?.(name),
      });
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
        percentage: total ? Math.round((detail.referrals / total) * 100) : 0,
      }));
    }

    getPercentage(metric: string, formatted?: boolean) {
      const value = (this[metric] as number) || 0;
      const share = this.pageviews ? value / this.pageviews : 0;
      return formatted ? `${Math.round(share * 100)}%` : share;
    }
  }

  (window as unknown as Record<string, unknown>).Piwik_Transitions_Ajax = FakeAjax;
  (window as unknown as Record<string, unknown>).Piwik_Transitions_Model = FakeModel;

  return backend;
}
