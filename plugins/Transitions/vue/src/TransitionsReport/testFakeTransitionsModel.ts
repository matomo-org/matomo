/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Test double for the legacy Piwik_Transitions_Model/Ajax pair, so specs can drive the component
 * without a backend.
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

type FakePart = 'internalReferrers'|'followingActions'|'externalReferrers';

export interface FakeTransitionsBackend {
  /** Resolves every outstanding part of the oldest pending load with the configured report. */
  respond(): void;
  /** Resolves every outstanding part of the newest pending load, leaving older ones outstanding. */
  respondNewest(): void;
  /** Resolves one part of the oldest pending load. */
  respondPart(part: FakePart): void;
  /** Fails the oldest pending load with an API exception name, via its internalReferrers part. */
  fail(errorName: string): void;
  /** Fails one part of the oldest pending load, leaving its other parts outstanding. */
  failPart(part: FakePart, errorName: string): void;
  /** Fails a request other than the report's own, the way the parallel total-pageviews one can. */
  failOtherRequest(errorName: string, method: string): void;
  /** Lands the total that `totalNbPageviews: false` leaves in flight. */
  resolveTotalNbPageviews(nbPageviews: number): void;
  /** How many callers are still waiting on the fire-once total. */
  totalWaiterCount(): number;
  /** How many loads have been started. */
  loadCount(): number;
  /** How many times the report aborted the requests of a load. */
  abortCount(): number;
  /** What each load was asked for, so a spec can pin what reaches the model. */
  loads: { actionType: string; actionName: string; overrideParams: Record<string, string>|null }[];
  /** Report to serve; may be swapped between loads. */
  report: FakeReport;
}

/** Mirrors Piwik_Transitions_Model.roundPercentage: whole percent from 10% up, one decimal below. */
function roundPercentage(share: number): number {
  return share < 0.1 ? Math.round(share * 1000) / 10 : Math.round(share * 100);
}

/** The groups each part fills, as Piwik_Transitions_Model splits a response. */
const GROUPS_BY_PART: Record<FakePart, string[]> = {
  internalReferrers: ['previousPages', 'previousSiteSearches'],
  followingActions: ['followingPages', 'followingSiteSearches', 'downloads', 'outlinks'],
  externalReferrers: ['searchEngines', 'socialNetworks', 'aiAssistants', 'websites', 'campaigns'],
};

const PART_ORDER: FakePart[] = ['internalReferrers', 'followingActions', 'externalReferrers'];

interface PendingPart {
  resolve: () => void;
  fail: (name: string) => void;
}

/** The part requests one model was asked for, i.e. one load of the report. */
interface PendingLoad {
  parts: Partial<Record<FakePart, PendingPart>>;
}

function settle(load: PendingLoad|undefined, part: FakePart, errorName?: string) {
  const pending = load?.parts[part];
  if (!load || !pending) {
    return;
  }

  delete load.parts[part];
  if (typeof errorName === 'string') {
    pending.fail(errorName);
  } else {
    pending.resolve();
  }
}

function settleAll(load: PendingLoad|undefined) {
  PART_ORDER.forEach((part) => settle(load, part));
}

/** Loads do not resolve on their own, so a spec can interleave several and order the finishes. */
export function installFakeTransitionsBackend(report: FakeReport = {}): FakeTransitionsBackend {
  let loads = 0;
  let aborts = 0;
  const loadArgs: FakeTransitionsBackend['loads'] = [];
  const queue: PendingLoad[] = [];
  const dropSettled = () => {
    while (queue.length && !Object.keys(queue[0].parts).length) {
      queue.shift();
    }
  };
  let liveAjax: { errorCallback: ((name: string, params: Record<string, unknown>) => void)|null }
    |null = null;
  const totalWaiters: ((nbPageviews: number|false) => void)[] = [];
  // Mirrors Piwik_Transitions_Model.totalNbPageviewsSettled. `totalNbPageviews: false` means the
  // request is still in flight, and specs set it after install, so read it lazily.
  let totalNotified = false;
  const isTotalSettled = () => totalNotified || report.totalNbPageviews !== false;

  const backend: FakeTransitionsBackend = {
    report,
    loadCount: () => loads,
    abortCount: () => aborts,
    loads: loadArgs,
    respond() {
      settleAll(queue.shift());
    },
    respondNewest() {
      settleAll(queue.pop());
    },
    respondPart(part: FakePart) {
      settle(queue[0], part);
      dropSettled();
    },
    fail(errorName: string) {
      settle(queue.shift(), 'internalReferrers', errorName);
    },
    failPart(part: FakePart, errorName: string) {
      settle(queue[0], part, errorName);
      dropSettled();
    },
    failOtherRequest(errorName: string, method: string) {
      liveAjax?.errorCallback?.(errorName, { method });
    },
    resolveTotalNbPageviews(nbPageviews: number) {
      backend.report.totalNbPageviews = nbPageviews;
      totalNotified = true;
      totalWaiters.splice(0).forEach((waiter) => waiter(nbPageviews));
    },
    totalWaiterCount: () => totalWaiters.length,
  };

  class FakeAjax {
    errorCallback: ((errorName: string, params: Record<string, unknown>) => void)|null = null;

    setErrorCallback(callback: (errorName: string, params: Record<string, unknown>) => void) {
      this.errorCallback = callback;
      liveAjax = this;
    }

    // eslint-disable-next-line class-methods-use-this
    abort() {
      aborts += 1;
    }
  }

  class FakeModel {
    ajax: FakeAjax;

    pendingLoad: PendingLoad|null = null;

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

    private apply(part: FakePart) {
      const data = backend.report;
      if (part === 'internalReferrers') {
        this.date = data.date ?? '2012-08-09';
        this.pageviews = data.pageviews ?? 0;
        this.loops = data.loops ?? 0;
      } else if (part === 'followingActions') {
        this.exits = data.exits ?? 0;
      } else {
        this.directEntries = data.directEntries ?? 0;
      }

      GROUPS_BY_PART[part].forEach((name) => {
        this[`${name}NbTransitions`] = data.groups?.[name]?.total ?? 0;
      });
    }

    loadPart(
      part: FakePart,
      actionType: string,
      actionName: string,
      overrideParams: Record<string, string>|null,
      callback: () => void,
    ) {
      if (!this.pendingLoad) {
        loads += 1;
        loadArgs.push({ actionType, actionName, overrideParams });
        this.pendingLoad = { parts: {} };
        queue.push(this.pendingLoad);
      }

      this.pendingLoad.parts[part] = {
        resolve: () => {
          this.apply(part);
          callback();
        },
        fail: (name: string) => this.ajax.errorCallback?.(
          name,
          { method: 'Transitions.getTransitionsForAction', parts: part },
        ),
      };
    }

    whenTotalNbPageviewsLoaded(callback: (nbPageviews: number|false) => void) {
      if (isTotalSettled()) {
        callback(this.getTotalNbPageviews());
        return;
      }

      totalWaiters.push(callback);
    }

    notifyTotalNbPageviewsLoaded(nbPageviews: number|false) {
      totalNotified = true;
      totalWaiters.splice(0).forEach((waiter) => waiter(nbPageviews));
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
