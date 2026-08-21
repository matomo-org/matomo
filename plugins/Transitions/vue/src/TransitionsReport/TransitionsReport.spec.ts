/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

const postEvent = vi.fn();

// Pulled in dynamically: a vi.mock() factory is hoisted above every import in the file, so it
// cannot reach a top-level one.
vi.mock('CoreHome', async () => {
  const { coreHomeMock } = await import('./testCoreHomeMock');
  return coreHomeMock((...args: unknown[]) => postEvent(...args));
});

import { flushRibbons, mountTransitionsReport } from './testTransitionsReportHarness';

import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';
import { stubElementRects, useSynchronousFrames } from './testMeasuredLayout';

const REPORT_WITH_DATA = {
  date: '2012-08-09',
  pageviews: 100,
  loops: 10,
  exits: 20,
  directEntries: 15,
  groups: {
    previousPages: {
      total: 40,
      details: [
        { url: 'http://example.org/a', referrals: 30 },
        { url: 'http://example.org/b', referrals: 10 },
      ],
    },
    searchEngines: {
      total: 25,
      details: [{ label: 'Google', referrals: 25 }],
    },
    followingPages: {
      total: 35,
      details: [{ url: 'http://example.org/c', referrals: 35 }],
    },
  },
};

describe('Transitions/TransitionsReport', () => {
  let backend: FakeTransitionsBackend;

  beforeEach(() => {
    postEvent.mockClear();
    stubElementRects();
    backend = installFakeTransitionsBackend(structuredClone(REPORT_WITH_DATA));
    useSynchronousFrames();
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  async function mountLoaded(props = {}): Promise<VueWrapper> {
    const wrapper = mountTransitionsReport(props);
    backend.respond();
    await flushRibbons();
    return wrapper;
  }

  it('should open the internal pages group on each side by default', async () => {
    const wrapper = await mountLoaded();

    const titles = wrapper.findAll('.transitionsSection__title').map((node) => node.text());
    expect(titles).toEqual([
      'Transitions_FromPreviousPages',
      'Transitions_OtherSources',
      'Transitions_ToFollowingPages',
    ]);
  });

  it('should not call the incoming block "other" when no group is open', async () => {
    // An entry page has no previous pages, so nothing opens on the incoming side and its single
    // block holds all of the incoming traffic.
    backend.report = {
      pageviews: 100,
      directEntries: 40,
      groups: {
        searchEngines: { total: 60, details: [{ label: 'Google', referrals: 60 }] },
        followingPages: {
          total: 35,
          details: [{ url: 'http://example.org/c', referrals: 35 }],
        },
      },
    };
    const wrapper = await mountLoaded();

    const titles = wrapper.findAll('.transitionsSection__title').map((node) => node.text());
    expect(titles).toEqual(['Transitions_IncomingTraffic', 'Transitions_ToFollowingPages']);
  });

  it('should list the detail rows of the open group', async () => {
    const wrapper = await mountLoaded();

    const rows = wrapper.findAll('.transitionsRow__label').map((node) => node.text());
    // Shortened for display: protocol, www and domain removed for internal page URLs. The
    // remaining rows are the summaries of the groups that are not open.
    expect(rows).toEqual([
      '/a',
      '/b',
      'Transitions_FromSearchEngines',
      'Transitions_DirectEntries',
      '/c',
      'General_ColumnExits',
    ]);
  });

  it('should show the share of each row in its pill', async () => {
    const wrapper = await mountLoaded();

    const pills = wrapper.findAll('.transitionsRow__pill').map((node) => node.text());
    expect(pills.slice(0, 2)).toEqual(['75%', '25%']);
    // Summary rows carry their share of all pageviews, in parentheses.
    expect(pills[2]).toBe('(25%)');
  });

  it('should put the full text behind the labels that truncate', async () => {
    const wrapper = await mountLoaded();

    // The card title is a shortened URL here, so the untruncated action name is worth a tooltip.
    expect(wrapper.find('.transitionsCenterCard__title').attributes('title'))
      .toBe('http://example.org/page');
    expect(wrapper.find('.transitionsSection__title').attributes('title'))
      .toBe('Transitions_FromPreviousPages');
  });

  it('should not repeat a page title that is already shown in full', async () => {
    const wrapper = await mountLoaded({ actionType: 'title', actionName: 'My page' });

    expect(wrapper.find('.transitionsCenterCard__title').text()).toBe('My page');
    expect(wrapper.find('.transitionsCenterCard__title').attributes('title')).toBeUndefined();
  });

  it('should round a summary percentage the way the rest of the report does', async () => {
    // 46.3% and 3.7% of the page's pageviews: the model rounds the first to a whole percent and
    // keeps a decimal on the second, and a summary row has to agree with the card's tooltip for
    // the same group rather than rounding on its own.
    backend.report = {
      pageviews: 1000,
      groups: {
        previousPages: { total: 100, details: [{ url: 'http://example.org/a', referrals: 100 }] },
        searchEngines: { total: 463, details: [{ label: 'Google', referrals: 463 }] },
        socialNetworks: { total: 37, details: [{ label: 'Mastodon', referrals: 37 }] },
      },
    };
    const wrapper = await mountLoaded();

    const pills = wrapper.findAll('.transitionsRow__pill').map((node) => node.text());
    // The open group's detail row carries its share within the group; the summary rows below it
    // carry their share of the page's pageviews, in parentheses.
    expect(pills).toEqual(['100%', '(46%)', '(3.7%)']);

    const tooltips = wrapper.findAll('.transitionsCenterCard__metric')
      .map((node) => node.attributes('title'));
    expect(tooltips).toContain('Transitions_XOfAllPageviews:46%');
    expect(tooltips).toContain('Transitions_XOfAllPageviews:3.7%');
  });

  it('should badge each section with its total', async () => {
    const wrapper = await mountLoaded();

    const badges = wrapper.findAll('.transitionsSection__badge').map((node) => node.text());
    expect(badges).toEqual([
      'Transitions_NumPageviews:40',
      'Transitions_NumPageviews:40', // search engines 25 + direct entries 15
      'Transitions_NumPageviews:35',
    ]);
  });

  it('should give each row a leading icon', async () => {
    const wrapper = await mountLoaded();

    const icons = wrapper.findAll('.transitionsRow__glyph').map((node) => node.classes());
    expect(icons[0]).toContain('icon-document');
    expect(icons[2]).toContain('icon-search');
    expect(icons[3]).toContain('icon-sign-in');
  });

  it('should list the center card metrics with their inline labels', async () => {
    const wrapper = await mountLoaded();

    const labels = wrapper.findAll('.transitionsCenterCard__metricLabel').map((n) => n.text());
    expect(labels).toContain('Transitions_FromPreviousPagesInline:40');
    expect(labels).toContain('Referrers_TypeDirectEntries:15');
    expect(labels).toContain('Transitions_ExitsInline:20');
    // Every metric is listed, including the ones with no transitions.
    expect(labels).toContain('Referrers_TypeSocialNetworks:0');
    expect(labels).toHaveLength(13);
  });

  it('should total each side above its metric list', async () => {
    const wrapper = await mountLoaded();

    const totals = wrapper.findAll('.transitionsCenterCard__metricTotal').map((n) => n.text());
    expect(totals).toEqual(['80', '55']); // 40 + 25 + 15 incoming, 35 + 20 outgoing
  });

  it('should mark metrics with no transitions with an empty dot', async () => {
    const wrapper = await mountLoaded();

    const dots = wrapper.findAll('.transitionsCenterCard__dot').map((n) => n.classes().join(' '));
    expect(dots[0]).toContain('transitionsCenterCard__dot--incoming');
    expect(dots[1]).toContain('transitionsCenterCard__dot--empty');
  });

  it('should show the pageviews badge and the loops line', async () => {
    const wrapper = await mountLoaded();

    expect(wrapper.find('.transitionsCenterCard__pageviews').text())
      .toBe('Transitions_NumPageviews:100');
    expect(wrapper.find('.transitionsCenterCard__loops').text())
      .toBe('Transitions_LoopsInline:10');
  });

  it('should fill the pageviews tooltip in once the site total arrives', async () => {
    // The total is fetched once per page load, in parallel with the first report, so on that
    // report it is still in flight when the data lands. The share is the point of the tooltip, so
    // it stays empty until then rather than showing a share of nothing.
    backend.report.totalNbPageviews = false;
    const wrapper = await mountLoaded();

    expect(wrapper.find('.transitionsCenterCard__pageviews').attributes('title')).toBe('');

    backend.resolveTotalNbPageviews(1000);
    await flushRibbons();

    expect(wrapper.find('.transitionsCenterCard__pageviews').attributes('title'))
      .toContain('Transitions_ShareOfAllPageviews');
  });

  it('should name what it is loading in the popover', () => {
    // The legacy popover's own loading state said "Loading Transitions for <page>"; the embedded
    // report's inline loader said the generic thing, so the message follows the context.
    const popover = mountTransitionsReport({ context: 'popover' });
    expect(popover.find('.activityIndicator').attributes('loading-message'))
      .toBe('General_LoadingPopoverFor:Transitions_Transitions http://example.org/page');

    const embedded = mountTransitionsReport();
    expect(embedded.find('.activityIndicator').attributes('loading-message'))
      .toBe('General_LoadingData');
  });

  it('should post Transitions.dataChanged once the data is in', async () => {
    await mountLoaded();

    expect(postEvent).toHaveBeenCalledWith(
      'Transitions.dataChanged',
      { actionType: 'url', actionName: 'http://example.org/page' },
    );
  });

  it('should draw one ribbon per visible row and one per collapsed group', async () => {
    const wrapper = await mountLoaded();

    // Incoming: 2 rows of the open previousPages group, plus the searchEngines and directEntries
    // summary rows. Outgoing: 1 row of followingPages plus the exits summary row.
    expect(wrapper.findAll('.transitionsRibbons__band')).toHaveLength(6);
  });

  describe('no data', () => {
    it('should render the translated no-data error instead of the grid', async () => {
      const wrapper = mountTransitionsReport();
      backend.fail('NoDataForAction');
      await nextTick();

      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__errorTitle').text())
        .toContain('Transitions_NoDataForAction');
      expect(wrapper.find('.transitionsReport__errorMessage').text())
        .toBe('Transitions_NoDataForActionDetails');
    });

    it('should offer the back link in the popover, where a history step closes it', async () => {
      const wrapper = mountTransitionsReport({ context: 'popover' });
      backend.fail('NoDataForAction');
      await nextTick();

      expect(wrapper.find('.transitionsReport__errorBack').text())
        .toBe('Transitions_ErrorBack');
    });

    it('should step back through history when the back link is clicked', async () => {
      const back = vi.spyOn(window.history, 'back').mockImplementation(() => {});
      const wrapper = mountTransitionsReport({ context: 'popover' });
      backend.fail('NoDataForAction');
      await nextTick();

      await wrapper.find('.transitionsReport__errorBack').trigger('click');

      expect(back).toHaveBeenCalled();
    });

    it('should leave the back link out of the embedded report', async () => {
      // On the Transitions page history.back() navigates away from the page rather than closing
      // anything, so the legacy renderer's inline error had no back link either.
      const wrapper = mountTransitionsReport();
      backend.fail('NoDataForAction');
      await nextTick();

      expect(wrapper.find('.transitionsReport__error').exists()).toBe(true);
      expect(wrapper.find('.transitionsReport__errorBack').exists()).toBe(false);
    });
  });

  describe('period not allowed', () => {
    it('should render the translated period-not-allowed error', async () => {
      const wrapper = mountTransitionsReport();
      backend.fail('PeriodNotAllowed');
      await nextTick();

      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__errorTitle').text())
        .toContain('Transitions_PeriodNotAllowed');
      expect(wrapper.find('.transitionsReport__errorMessage').text())
        .toBe('Transitions_PeriodNotAllowedDetails');
    });

    it('should clear the error when a later load succeeds', async () => {
      const wrapper = mountTransitionsReport();
      backend.fail('PeriodNotAllowed');
      await nextTick();
      expect(wrapper.find('.transitionsReport__error').exists()).toBe(true);

      await wrapper.setProps({ actionName: 'http://example.org/other' });
      backend.respond();
      await flushRibbons();

      expect(wrapper.find('.transitionsReport__error').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);
    });

  });

  it('should still translate a known error that arrives with a stack trace appended', async () => {
    const wrapper = mountTransitionsReport();
    backend.fail('NoDataForAction #0 [internal function]: Piwik\\Plugins\\Transitions\\API->x()');
    await nextTick();

    expect(wrapper.find('.transitionsReport__errorTitle').text())
      .toContain('Transitions_NoDataForAction');
    expect(wrapper.find('.transitionsReport__errorMessage').text())
      .toBe('Transitions_NoDataForActionDetails');
  });

  it('should show an unknown exception as-is', async () => {
    const wrapper = mountTransitionsReport({ context: 'popover' });
    backend.fail('Something went wrong');
    await nextTick();

    expect(wrapper.find('.transitionsReport__errorTitle').text()).toBe('Something went wrong');
    expect(wrapper.find('.transitionsReport__errorMessage').exists()).toBe(false);
    // The name is all we can say about the error, but the back link still has to read as one.
    expect(wrapper.find('.transitionsReport__errorBack').text()).toBe('Transitions_ErrorBack');
  });

  it('should keep the report when a request other than its own fails', async () => {
    const wrapper = mountTransitionsReport();
    backend.respond();
    await flushRibbons();
    expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);

    // The site's total pageviews travel over the same ajax instance, so its failure reaches the
    // report's error callback too. It must not replace a report that loaded fine.
    backend.failOtherRequest('Whatever', 'Actions.get');
    await flushRibbons();

    expect(wrapper.find('.transitionsReport__error').exists()).toBe(false);
    expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);
  });
});
