/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

type Params = Record<string, unknown>;

const { fetch, matomo } = vi.hoisted(() => ({
  fetch: vi.fn(),
  matomo: {
    helper: { isReportingPage: vi.fn(() => true) },
    postEvent: vi.fn(),
  },
}));

vi.mock('CoreHome', () => ({
  AjaxHelper: { fetch },
  Matomo: matomo,
}));

// eslint-disable-next-line import/first
import StarredGoalsStore, { REFRESH_REPORTING_LISTS_DELAY_MS } from './StarredGoals.store';

interface PendingRequest {
  params: Params;
  options: Params;
  resolve: () => void;
  reject: () => void;
}

let requests: PendingRequest[];

// lets the promise chains of the store run
function flush(): Promise<void> {
  return Array.from({ length: 10 }).reduce<Promise<void>>(
    (promise) => promise.then(() => undefined),
    Promise.resolve(),
  );
}

function refreshEvents() {
  return matomo.postEvent.mock.calls.filter(([name]) => name === 'updateReportingMenu');
}

describe('Goals/StarredGoalsStore', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    requests = [];
    fetch.mockReset();
    fetch.mockImplementation((params: Params, options: Params) => new Promise<void>(
      (resolve, reject) => {
        requests.push({
          params, options, resolve, reject,
        });
      },
    ));
    matomo.postEvent.mockReset();
    matomo.helper.isReportingPage.mockReturnValue(true);
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('sends a request that a navigation cannot abort, for the site of the click', async () => {
    const saved = StarredGoalsStore.setGoalStarred(3, 7, true, false);
    await flush();

    expect(requests).toHaveLength(1);
    expect(requests[0].params).toEqual({ method: 'Goals.starGoal', idSite: 3, idGoal: 7 });
    expect(requests[0].options).toEqual({ abortable: false });

    requests[0].resolve();
    await expect(saved).resolves.toBe(true);
  });

  it('sends the changes of one goal in the order of the clicks', async () => {
    const first = StarredGoalsStore.setGoalStarred(1, 2, true, false);
    const second = StarredGoalsStore.setGoalStarred(1, 2, false, true);
    await flush();

    expect(requests.map((request) => request.params.method)).toEqual(['Goals.starGoal']);

    requests[0].resolve();
    await flush();
    expect(requests.map((request) => request.params.method)).toEqual(['Goals.starGoal', 'Goals.unstarGoal']);

    requests[1].resolve();
    await expect(first).resolves.toBeUndefined();
    await expect(second).resolves.toBe(false);
  });

  it('returns the previous state when saving fails and no later change is waiting', async () => {
    const saved = StarredGoalsStore.setGoalStarred(1, 4, true, false);
    await flush();
    requests[0].reject();

    await expect(saved).resolves.toBe(false);
  });

  it('leaves the state to a later change when an earlier one fails', async () => {
    const first = StarredGoalsStore.setGoalStarred(1, 5, true, false);
    const second = StarredGoalsStore.setGoalStarred(1, 5, false, true);
    await flush();
    requests[0].reject();
    await flush();
    requests[1].resolve();

    await expect(first).resolves.toBeUndefined();
    await expect(second).resolves.toBe(false);
  });

  it('reloads the reporting menu and the widget list once, shortly after the last save', async () => {
    StarredGoalsStore.setGoalStarred(1, 1, true, false);
    StarredGoalsStore.setGoalStarred(1, 2, true, false);
    await flush();

    requests[0].resolve();
    await flush();
    vi.advanceTimersByTime(REFRESH_REPORTING_LISTS_DELAY_MS);
    expect(refreshEvents()).toHaveLength(0);

    requests[1].resolve();
    await flush();
    vi.advanceTimersByTime(REFRESH_REPORTING_LISTS_DELAY_MS - 1);
    expect(refreshEvents()).toHaveLength(0);
    vi.advanceTimersByTime(1);
    expect(refreshEvents()).toHaveLength(1);
  });

  it('still reloads the lists when a save is followed by a failed one', async () => {
    StarredGoalsStore.setGoalStarred(1, 1, true, false);
    StarredGoalsStore.setGoalStarred(1, 2, true, false);
    await flush();

    requests[0].resolve();
    await flush();
    requests[1].reject();
    await flush();
    vi.advanceTimersByTime(REFRESH_REPORTING_LISTS_DELAY_MS);

    expect(refreshEvents()).toHaveLength(1);
  });

  it('does not reload the lists when nothing was saved', async () => {
    StarredGoalsStore.setGoalStarred(1, 1, true, false);
    await flush();
    requests[0].reject();
    await flush();
    vi.advanceTimersByTime(REFRESH_REPORTING_LISTS_DELAY_MS);

    expect(refreshEvents()).toHaveLength(0);
  });

  it('does not reload the lists outside of the reporting pages', async () => {
    matomo.helper.isReportingPage.mockReturnValue(false);
    StarredGoalsStore.setGoalStarred(1, 1, true, false);
    await flush();
    requests[0].resolve();
    await flush();
    vi.advanceTimersByTime(REFRESH_REPORTING_LISTS_DELAY_MS);

    expect(refreshEvents()).toHaveLength(0);
  });
});
