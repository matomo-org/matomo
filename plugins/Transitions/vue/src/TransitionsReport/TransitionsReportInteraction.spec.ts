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
    helper: { addBreakpointsToUrl: (url: string) => url },
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
    formatPercent: (value: number) => `${value}%`,
  },
  translate: (key: string) => key,
}));

import TransitionsReport from './TransitionsReport.vue';
import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';

const REPORT = {
  pageviews: 100,
  loops: 0,
  exits: 10,
  directEntries: 5,
  groups: {
    previousPages: {
      total: 40,
      details: [{ url: 'http://example.org/a', referrals: 40 }],
    },
    searchEngines: {
      total: 25,
      details: [{ label: 'Google', referrals: 20 }, { label: 'Bing', referrals: 5 }],
    },
    followingPages: {
      total: 20,
      details: [{ url: 'http://example.org/c', referrals: 20 }],
    },
    outlinks: {
      total: 8,
      details: [{ url: 'http://other.example/x', referrals: 8 }],
    },
  },
};

const measure = () => ({ top: 0, height: 100, width: 100 });

describe('Transitions/TransitionsReport interaction', () => {
  let backend: FakeTransitionsBackend;

  beforeEach(() => {
    postEvent.mockClear();
    backend = installFakeTransitionsBackend(structuredClone(REPORT));
    vi.stubGlobal('requestAnimationFrame', (callback: FrameRequestCallback) => {
      callback(0);
      return 1;
    });
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  async function flush() {
    for (let i = 0; i < 5; i += 1) {
      // eslint-disable-next-line no-await-in-loop
      await nextTick();
    }
  }

  async function mountLoaded(props = {}): Promise<VueWrapper> {
    const wrapper = mount(TransitionsReport as any, {
      props: {
        actionType: 'url',
        actionName: 'http://example.org/page',
        measure,
        ...props,
      },
      global: { config: { globalProperties: { $sanitize: (value: string) => value } } },
    });
    backend.respond();
    await flush();
    return wrapper;
  }

  /** Finds a section by its (untranslated) heading text. */
  function sectionByTitle(wrapper: VueWrapper, title: string) {
    return wrapper.findAll('.transitionsSection').find(
      (section) => section.find('.transitionsSection__title').exists()
        && section.find('.transitionsSection__title').text() === title,
    );
  }

  /** Finds a summary row by its (untranslated) label. */
  function summaryRow(wrapper: VueWrapper, label: string) {
    return wrapper.findAll('.transitionsRow--summary').find(
      (row) => row.find('.transitionsRow__label').text() === label,
    )!;
  }

  /** The labels of the detail rows of the open section on a side. */
  function openRowLabels(wrapper: VueWrapper, title: string): string[] {
    const section = sectionByTitle(wrapper, title);
    return section
      ? section.findAll('.transitionsRow__label').map((node) => node.text())
      : [];
  }

  it('should open a group when its summary row is clicked', async () => {
    const wrapper = await mountLoaded();

    expect(sectionByTitle(wrapper, 'Transitions_FromSearchEngines')).toBeUndefined();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flush();

    expect(openRowLabels(wrapper, 'Transitions_FromSearchEngines')).toEqual(['Google', 'Bing']);
  });

  it('should move the previously open group into the catch-all block', async () => {
    const wrapper = await mountLoaded();

    expect(openRowLabels(wrapper, 'Transitions_FromPreviousPages')).toEqual(['/a']);

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flush();

    expect(sectionByTitle(wrapper, 'Transitions_FromPreviousPages')).toBeUndefined();
    expect(summaryRow(wrapper, 'Transitions_FromPreviousPages').exists()).toBe(true);
  });

  it('should leave the other side untouched when a group is opened', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flush();

    expect(openRowLabels(wrapper, 'Transitions_ToFollowingPages')).toEqual(['/c']);
  });

  it('should not offer to open a group that has no detail rows', async () => {
    const wrapper = await mountLoaded();

    const exits = summaryRow(wrapper, 'General_ColumnExits');
    expect(exits.classes()).not.toContain('transitionsRow--actionable');

    await exits.trigger('click');
    await flush();

    expect(openRowLabels(wrapper, 'Transitions_ToFollowingPages')).toEqual(['/c']);
  });

  it('should open a group when its center card metric is clicked', async () => {
    const wrapper = await mountLoaded();

    const outlinks = wrapper.findAll('.transitionsCenterCard__metric').find(
      (metric) => metric.text().includes('Transitions_NumOutlinks'),
    )!;

    await outlinks.trigger('click');
    await flush();

    expect(openRowLabels(wrapper, 'General_Outlinks')).toEqual(['other.example/x']);
  });

  it('should not open a metric that has no detail rows', async () => {
    const wrapper = await mountLoaded();

    const exits = wrapper.findAll('.transitionsCenterCard__metric').find(
      (metric) => metric.text().includes('Transitions_ExitsInline'),
    )!;

    expect(exits.classes()).not.toContain('transitionsCenterCard__metric--actionable');

    await exits.trigger('click');
    await flush();

    expect(openRowLabels(wrapper, 'Transitions_ToFollowingPages')).toEqual(['/c']);
  });

  it('should highlight the ribbon of a summary row when it is hovered', async () => {
    const wrapper = await mountLoaded();

    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(0);

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('mouseenter');
    await flush();

    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(1);

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('mouseleave');
    await flush();

    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(0);
  });

  it('should highlight every ribbon of the open group when its metric is hovered', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flush();

    const searchEngines = wrapper.findAll('.transitionsCenterCard__metric').find(
      (metric) => metric.text().includes('Referrers_TypeSearchEngines'),
    )!;

    await searchEngines.trigger('mouseenter');
    await flush();

    // Both detail rows of the now-open group light up.
    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(2);
  });

  it('should post switchTransitionsUrl when an internal page row is clicked', async () => {
    const wrapper = await mountLoaded();

    await wrapper.find('.transitionsRow').trigger('click');

    expect(postEvent).toHaveBeenCalledWith(
      'Transitions.switchTransitionsUrl',
      { url: 'http://example.org/a' },
    );
  });

  it('should emit navigate instead when mounted in a popover', async () => {
    const wrapper = await mountLoaded({ context: 'popover' });

    await wrapper.find('.transitionsRow').trigger('click');

    expect(postEvent).not.toHaveBeenCalledWith(
      'Transitions.switchTransitionsUrl',
      expect.anything(),
    );
    expect(wrapper.emitted('navigate')).toEqual([['http://example.org/a']]);
  });

  it('should render an external row as a link and not navigate the report', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'General_Outlinks').trigger('click');
    await flush();

    const row = sectionByTitle(wrapper, 'General_Outlinks')!.find('.transitionsRow');
    expect(row.element.tagName).toBe('A');
    expect(row.attributes('href')).toBe('http://other.example/x');
    expect(row.attributes('rel')).toBe('noreferrer noopener');

    await row.trigger('click');
    expect(wrapper.emitted('navigate')).toBeUndefined();
  });

  it('should reset the open groups when the action changes', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flush();

    await wrapper.setProps({ actionName: 'http://example.org/other' });
    backend.respond();
    await flush();

    expect(openRowLabels(wrapper, 'Transitions_FromPreviousPages')).toEqual(['/a']);
    expect(sectionByTitle(wrapper, 'Transitions_FromSearchEngines')).toBeUndefined();
  });
});
