/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { AjaxHelper, Matomo } from 'CoreHome';

// how long to wait after the last star was saved before the menu and the widget list are reloaded
export const REFRESH_REPORTING_LISTS_DELAY_MS = 500;

// The requests for one goal are sent one after another, so they are applied in the order of the
// clicks.
interface GoalRequestQueue {
  lastRequest: Promise<unknown>;
  numPending: number;
  savedState: boolean;
}

/**
 * Saves the starred state of goals.
 *
 * Lives outside the Manage Goals component on purpose: a save that is still running when the user
 * opens a report must neither be cancelled nor lose the reload of the reporting menu and the widget
 * list that follows it.
 */
class StarredGoalsStore {
  private queues: Record<string, GoalRequestQueue> = {};

  private numPending = 0;

  // whether a star was saved that the reporting menu and the widget list do not show yet
  private hasSavedChanges = false;

  private refreshTimeout?: number;

  /**
   * Saves whether a goal is starred.
   *
   * `currentState` is the state the goal had before this change. The returned promise resolves
   * once the request is done. When no other change of the same goal is waiting by then, it
   * resolves with the state the goal has on the server, which is the previous state if saving
   * failed. Otherwise it resolves with `undefined` and the later change decides.
   */
  setGoalStarred(
    idSite: string|number,
    idGoal: string|number,
    starred: boolean,
    currentState: boolean,
  ): Promise<boolean|undefined> {
    const key = `${idSite}-${idGoal}`;
    const queue = this.queues[key] || {
      lastRequest: Promise.resolve(),
      numPending: 0,
      savedState: currentState,
    };
    this.queues[key] = queue;

    queue.numPending += 1;
    this.numPending += 1;

    const request = queue.lastRequest.then(() => AjaxHelper.fetch({
      method: starred ? 'Goals.starGoal' : 'Goals.unstarGoal',
      // the site at the time of the click, the page may show another site when the request is sent
      idSite,
      idGoal,
    }, {
      // not part of the global request queue, which is aborted when another report is opened
      abortable: false,
    })).then(() => {
      queue.savedState = starred;
      this.hasSavedChanges = true;
    }, () => undefined); // AjaxHelper has shown the error notification

    queue.lastRequest = request;

    return request.then(() => {
      queue.numPending -= 1;
      this.numPending -= 1;

      if (this.numPending === 0 && this.hasSavedChanges) {
        this.hasSavedChanges = false;
        this.scheduleReportingListsRefresh();
      }

      if (queue.numPending > 0) {
        return undefined;
      }

      delete this.queues[key];
      return queue.savedState;
    });
  }

  // The reporting menu and the widget selector list starred goals first and only know the state
  // from when they were loaded. They are reloaded once, shortly after the last star was saved, so
  // starring several goals in a row does not reload them for every goal.
  private scheduleReportingListsRefresh() {
    window.clearTimeout(this.refreshTimeout);
    this.refreshTimeout = window.setTimeout(() => {
      this.refreshTimeout = undefined;

      if (Matomo.helper.isReportingPage()) {
        // reloads the menu, keeps the open page selected, and reloads the widget list
        Matomo.postEvent('updateReportingMenu');
      }
    }, REFRESH_REPORTING_LISTS_DELAY_MS);
  }
}

export default new StarredGoalsStore();
