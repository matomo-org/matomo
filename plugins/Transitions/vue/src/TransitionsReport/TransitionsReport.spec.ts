/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

const postEvent = vi.fn();

vi.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div class="activityIndicator" />', props: ['loading'] },
  Matomo: {
    postEvent: (...args: unknown[]) => postEvent(...args),
    helper: {
      addBreakpointsToUrl: (url: string) => url,
    },
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
    formatPercent: (value: number) => `${value}%`,
  },
  // Substitutes %s and %1$s style placeholders, like Matomo's translate() does.
  translate: (key: string, ...args: string[]) => {
    let index = 0;
    return `${key}`.concat(args.length ? `:${args.join('|')}` : '')
      .replace(/%(\d+\$)?s/g, () => {
        const value = args[index];
        index += 1;
        return value;
      });
  },
}));

import TransitionsReport from './TransitionsReport.vue';
import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';

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

/** jsdom reports every element as 0x0, so hand the ribbon layer usable rects. */
const measure = () => ({ top: 0, height: 100, width: 100 });

describe('Transitions/TransitionsReport', () => {
  let backend: FakeTransitionsBackend;

  beforeEach(() => {
    postEvent.mockClear();
    backend = installFakeTransitionsBackend(structuredClone(REPORT_WITH_DATA));
    // Run the ribbon layer's scheduled measurement synchronously, so a rendered frame is enough.
    vi.stubGlobal('requestAnimationFrame', (callback: FrameRequestCallback) => {
      callback(0);
      return 1;
    });
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  function mountReport(props = {}) {
    return mount(TransitionsReport as any, {
      props: {
        actionType: 'url',
        actionName: 'http://example.org/page',
        measure,
        ...props,
      },
      global: {
        config: {
          globalProperties: {
            $sanitize: (value: string) => value,
          },
        },
      },
    });
  }

  /** The grid, then the resolved element refs, then the ribbon paths each take a render. */
  async function flush() {
    for (let i = 0; i < 5; i += 1) {
      // eslint-disable-next-line no-await-in-loop
      await nextTick();
    }
  }

  async function mountLoaded(props = {}): Promise<VueWrapper> {
    const wrapper = mountReport(props);
    backend.respond();
    await flush();
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
      const wrapper = mountReport();
      backend.fail('NoDataForAction');
      await nextTick();

      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__errorTitle').text())
        .toContain('Transitions_NoDataForAction');
      expect(wrapper.find('.transitionsReport__errorMessage').text())
        .toBe('Transitions_NoDataForActionDetails');
      expect(wrapper.find('.transitionsReport__errorBack').text())
        .toBe('Transitions_ErrorBack');
    });
  });

  describe('period not allowed', () => {
    it('should render the translated period-not-allowed error', async () => {
      const wrapper = mountReport();
      backend.fail('PeriodNotAllowed');
      await nextTick();

      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__errorTitle').text())
        .toContain('Transitions_PeriodNotAllowed');
      expect(wrapper.find('.transitionsReport__errorMessage').text())
        .toBe('Transitions_PeriodNotAllowedDetails');
    });

    it('should clear the error when a later load succeeds', async () => {
      const wrapper = mountReport();
      backend.fail('PeriodNotAllowed');
      await nextTick();
      expect(wrapper.find('.transitionsReport__error').exists()).toBe(true);

      await wrapper.setProps({ actionName: 'http://example.org/other' });
      backend.respond();
      await flush();

      expect(wrapper.find('.transitionsReport__error').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);
    });
  });

  it('should still translate a known error that arrives with a stack trace appended', async () => {
    const wrapper = mountReport();
    backend.fail('NoDataForAction #0 [internal function]: Piwik\\Plugins\\Transitions\\API->x()');
    await nextTick();

    expect(wrapper.find('.transitionsReport__errorTitle').text())
      .toContain('Transitions_NoDataForAction');
    expect(wrapper.find('.transitionsReport__errorMessage').text())
      .toBe('Transitions_NoDataForActionDetails');
  });

  it('should show an unknown exception as-is', async () => {
    const wrapper = mountReport();
    backend.fail('Something went wrong');
    await nextTick();

    expect(wrapper.find('.transitionsReport__errorTitle').text()).toBe('Something went wrong');
    expect(wrapper.find('.transitionsReport__errorMessage').exists()).toBe(false);
  });
});
