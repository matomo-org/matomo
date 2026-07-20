/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import ReportingPage from './ReportingPage.vue';
import ReportingPageStoreInstance from './ReportingPage.store';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Matomo from '../Matomo/Matomo';
import { NotificationsStore } from '../Notification';

// The component cannot be mounted in jsdom (it loads the SitesManager.SiteWithoutData
// component on demand), so the gate decision and the render guard are exercised directly
// against the component options with a controlled `this`.
const options = ReportingPage as unknown as {
  computed: { showEmptySiteScreen(this: unknown): boolean };
  methods: { renderPage(this: unknown, ...args: string[]): void };
};

function setActiveGroup(group: string) {
  MatomoUrl.url.value = new URL(`http://localhost/index.php#?group=${group}`);
}

function gateContext(overrides: Record<string, unknown>) {
  return {
    siteHasNoData: false,
    noDataDismissed: false,
    groupsWithoutTrackingRequirement: ['CoreHome_AIInsights'],
    ...overrides,
  };
}

describe('CoreHome/ReportingPage', () => {
  afterEach(() => {
    jest.restoreAllMocks();
    MatomoUrl.url.value = new URL('http://localhost/index.php');
  });

  describe('showEmptySiteScreen', () => {
    function showEmptySiteScreen(overrides: Record<string, unknown>) {
      return options.computed.showEmptySiteScreen.call(gateContext(overrides));
    }

    it('does not show the screen when the site has data', () => {
      setActiveGroup('');
      expect(showEmptySiteScreen({ siteHasNoData: false })).toBe(false);
    });

    it('does not show the screen once it has been dismissed', () => {
      setActiveGroup('');
      expect(showEmptySiteScreen({ siteHasNoData: true, noDataDismissed: true })).toBe(false);
    });

    it('shows the screen on the default group when the site has no data', () => {
      setActiveGroup('');
      expect(showEmptySiteScreen({ siteHasNoData: true })).toBe(true);
    });

    it('skips the screen for a group exempt from the tracking requirement', () => {
      setActiveGroup('CoreHome_AIInsights');
      expect(showEmptySiteScreen({ siteHasNoData: true })).toBe(false);
    });

    it('still shows the screen for a non-exempt group while the site has no data', () => {
      setActiveGroup('Some_OtherGroup');
      expect(showEmptySiteScreen({ siteHasNoData: true })).toBe(true);
    });
  });

  describe('renderPage while the empty-site gate is shown', () => {
    it('clears stale transient notifications but neither loads a report nor changes the page', () => {
      const clearTransient = jest.spyOn(NotificationsStore, 'clearTransientNotifications')
        .mockImplementation(() => undefined);
      const fetchPage = jest.spyOn(ReportingPageStoreInstance, 'fetchPage')
        .mockResolvedValue(undefined as never);
      const postEvent = jest.spyOn(Matomo, 'postEvent').mockImplementation(() => undefined);

      const ctx = { showEmptySiteScreen: true, loading: true };
      options.methods.renderPage.call(
        ctx, 'General_Visitors', 'General_Overview', 'day', '2024-01-01', '',
      );

      expect(clearTransient).toHaveBeenCalledTimes(1);
      expect(ctx.loading).toBe(false);
      // a matomoPageChange would abort the requests the just-mounted gate component fires
      expect(postEvent).not.toHaveBeenCalled();
      expect(fetchPage).not.toHaveBeenCalled();
    });

    it('renders the report normally when the gate is not shown', () => {
      const clearTransient = jest.spyOn(NotificationsStore, 'clearTransientNotifications')
        .mockImplementation(() => undefined);
      const resetPage = jest.spyOn(ReportingPageStoreInstance, 'resetPage')
        .mockImplementation(() => undefined);

      // empty category short-circuits past the gate guard into the normal "no page" handling
      const ctx = { showEmptySiteScreen: false, loading: true };
      options.methods.renderPage.call(ctx, '', '', 'day', '2024-01-01', '');

      expect(resetPage).toHaveBeenCalledTimes(1);
      expect(clearTransient).not.toHaveBeenCalled();
      expect(ctx.loading).toBe(false);
    });
  });
});
