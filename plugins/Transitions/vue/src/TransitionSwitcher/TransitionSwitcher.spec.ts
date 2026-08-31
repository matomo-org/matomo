/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount, VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

// The factories below are hoisted above this file's imports, but they only close over state --
// nothing reads it until a test calls in, by which point this assignment has run.
const state = {
  fetchResult: Promise.resolve([]) as Promise<unknown>,
  listeners: {} as Record<string, ((params: unknown) => void)[]>,
};
const { listeners } = state;

jest.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div class="activityIndicator" />', props: ['loading'] },
  MatomoLoader: { template: '<div class="matomoLoader" />' },
  AjaxHelper: {
    fetch: () => state.fetchResult,
  },
  Matomo: {
    postEvent: () => undefined,
    on: (name: string, callback: (params: unknown) => void) => {
      state.listeners[name] = state.listeners[name] || [];
      state.listeners[name].push(callback);
    },
    off: (name: string, callback: (params: unknown) => void) => {
      state.listeners[name] = (state.listeners[name] || []).filter(
        (entry) => entry !== callback,
      );
    },
    helper: { addBreakpointsToUrl: (url: string) => url },
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
    formatPercent: (value: number) => `${value}%`,
  },
  translate: (key: string, ...args: string[]) => [key, ...args].join(':'),
}), { virtual: true });

jest.mock('CorePluginsAdmin', () => ({
  Field: {
    name: 'Field',
    template: '<div class="field" />',
    props: ['uicontrol', 'name', 'modelValue', 'title', 'fullWidth', 'disabled', 'options'],
    emits: ['update:modelValue'],
  },
}), { virtual: true });

import TransitionSwitcher from './TransitionSwitcher.vue';

const REPORT_ROWS = [
  { label: 'Page A', nb_hits: 10, url: 'http://example.org/a', segment: '' },
  { label: 'Page B', nb_hits: 5, url: 'http://example.org/b', segment: '' },
];

describe('Transitions/TransitionSwitcher', () => {
  beforeEach(() => {
    Object.keys(listeners).forEach((key) => delete listeners[key]);
    state.fetchResult = Promise.resolve(JSON.parse(JSON.stringify(REPORT_ROWS)));
  });

  async function mountSwitcher(): Promise<VueWrapper> {
    const wrapper = mount(TransitionSwitcher as any, {
      global: {
        config: {
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          globalProperties: {
            $sanitize: (value: string) => value,
            translate: (key: string, ...args: string[]) => [key, ...args].join(':'),
          } as any,
        },
        stubs: {
          TransitionsReport: {
            name: 'TransitionsReport',
            template: '<div class="reportStub" />',
            props: ['actionType', 'actionName', 'context'],
          },
        },
      },
    });

    await flushPromises();

    return wrapper as unknown as VueWrapper;
  }

  function reportStub(wrapper: VueWrapper) {
    return wrapper.findComponent({ name: 'TransitionsReport' });
  }

  /** The two selects, in template order: the report to list, then the action within it. */
  function selects(wrapper: VueWrapper) {
    const fields = wrapper.findAllComponents({ name: 'Field' });
    expect(fields.map((field) => field.props('name'))).toEqual(['actionType', 'actionName']);
    return { actionType: fields[0], actionName: fields[1] };
  }

  it('should mount the report for the first action of the report', async () => {
    const wrapper = await mountSwitcher();

    expect(reportStub(wrapper).exists()).toBe(true);
    expect(reportStub(wrapper).props()).toMatchObject({
      actionType: 'url',
      actionName: 'http://example.org/a',
      context: 'embedded',
    });
  });

  it('should swap the report props when another action is selected', async () => {
    const wrapper = await mountSwitcher();

    // Driven through the select rather than setData, so the v-model binding is covered too: a
    // field wired to the wrong data property would otherwise still pass.
    selects(wrapper).actionName.vm.$emit('update:modelValue', 'http://example.org/b');
    await nextTick();

    expect(reportStub(wrapper).props('actionName')).toBe('http://example.org/b');
  });

  it('should offer every action of the report as an option', async () => {
    const wrapper = await mountSwitcher();
    const { actionType, actionName } = selects(wrapper);

    expect(actionName.props('modelValue')).toBe('http://example.org/a');
    expect(actionName.props('options')).toEqual([
      {
        key: 'http://example.org/a',
        url: 'http://example.org/a',
        value: 'Page A (Transitions_NumPageviews:10)',
      },
      {
        key: 'http://example.org/b',
        url: 'http://example.org/b',
        value: 'Page B (Transitions_NumPageviews:5)',
      },
    ]);
    expect(actionType.props('modelValue')).toBe('Actions.getPageUrls');
  });

  it('should switch to the title report and pass the matching action type', async () => {
    const wrapper = await mountSwitcher();

    state.fetchResult = Promise.resolve([{ label: 'Some title', nb_hits: 3, url: '', segment: '' }]);
    selects(wrapper).actionType.vm.$emit('update:modelValue', 'Actions.getPageTitles');
    await nextTick();
    await flushPromises();

    expect(reportStub(wrapper).props()).toMatchObject({
      actionType: 'title',
      actionName: 'Some title',
    });
  });

  it('should follow a switchTransitionsUrl event to the matching option', async () => {
    const wrapper = await mountSwitcher();

    listeners['Transitions.switchTransitionsUrl'][0]({ url: 'http://example.org/b' });
    await nextTick();

    expect(reportStub(wrapper).props('actionName')).toBe('http://example.org/b');
  });

  it('should add an option for a page that is not in the top 100', async () => {
    const wrapper = await mountSwitcher();

    listeners['Transitions.switchTransitionsUrl'][0]({ url: 'http://example.org/deep' });
    await nextTick();

    expect(reportStub(wrapper).props('actionName')).toBe('example.org/deep');
    expect(selects(wrapper).actionName.props('options')).toHaveLength(3);
  });

  it('should disable the selector and render no report when there is no data', async () => {
    state.fetchResult = Promise.resolve([]);
    const wrapper = await mountSwitcher();
    const { actionName } = selects(wrapper);

    expect(actionName.props('disabled')).toBe(true);
    expect(actionName.props('options')).toEqual([
      { key: '_____ignore_____', value: 'CoreHome_ThereIsNoDataForThisReport' },
    ]);
    expect(reportStub(wrapper).exists()).toBe(false);
  });

  it('should render no report when the request fails', async () => {
    state.fetchResult = Promise.reject(new Error('nope'));
    const wrapper = await mountSwitcher();

    expect(selects(wrapper).actionName.props('disabled')).toBe(true);
    expect(reportStub(wrapper).exists()).toBe(false);
  });

  it('should stop listening for switchTransitionsUrl once unmounted', async () => {
    const wrapper = await mountSwitcher();
    expect(listeners['Transitions.switchTransitionsUrl']).toHaveLength(1);

    wrapper.unmount();

    expect(listeners['Transitions.switchTransitionsUrl']).toHaveLength(0);
  });
});
