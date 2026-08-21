/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

// Hoisted so the mock factory, which vitest lifts above this file's imports, can reach them.
const state = vi.hoisted(() => ({
  fetchResult: Promise.resolve([]) as Promise<unknown>,
  listeners: {} as Record<string, ((params: unknown) => void)[]>,
}));
const { listeners } = state;

vi.mock('CoreHome', () => ({
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
}));

vi.mock('CorePluginsAdmin', () => ({
  Field: {
    template: '<div class="field" />',
    props: ['uicontrol', 'name', 'modelValue', 'title', 'fullWidth', 'disabled', 'options'],
  },
}));

import TransitionSwitcher from './TransitionSwitcher.vue';

const REPORT_ROWS = [
  { label: 'Page A', nb_hits: 10, url: 'http://example.org/a', segment: '' },
  { label: 'Page B', nb_hits: 5, url: 'http://example.org/b', segment: '' },
];

describe('Transitions/TransitionSwitcher', () => {
  beforeEach(() => {
    Object.keys(listeners).forEach((key) => delete listeners[key]);
    state.fetchResult = Promise.resolve(structuredClone(REPORT_ROWS));
  });

  async function mountSwitcher(): Promise<VueWrapper> {
    const wrapper = mount(TransitionSwitcher as any, {
      global: {
        config: {
          globalProperties: {
            $sanitize: (value: string) => value,
            translate: (key: string, ...args: string[]) => [key, ...args].join(':'),
          },
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

    for (let i = 0; i < 4; i += 1) {
      // eslint-disable-next-line no-await-in-loop
      await nextTick();
    }

    return wrapper;
  }

  function reportStub(wrapper: VueWrapper) {
    return wrapper.findComponent({ name: 'TransitionsReport' });
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

    await wrapper.setData({ actionName: 'http://example.org/b' });

    expect(reportStub(wrapper).props('actionName')).toBe('http://example.org/b');
  });

  it('should switch to the title report and pass the matching action type', async () => {
    const wrapper = await mountSwitcher();

    state.fetchResult = Promise.resolve([{ label: 'Some title', nb_hits: 3, url: '', segment: '' }]);
    await wrapper.setData({ actionType: 'Actions.getPageTitles' });
    for (let i = 0; i < 4; i += 1) {
      // eslint-disable-next-line no-await-in-loop
      await nextTick();
    }

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
    expect((wrapper.vm as any).actionNameOptions).toHaveLength(3);
  });

  it('should disable the selector and render no report when there is no data', async () => {
    state.fetchResult = Promise.resolve([]);
    const wrapper = await mountSwitcher();

    expect((wrapper.vm as any).isEnabled).toBe(false);
    expect(reportStub(wrapper).exists()).toBe(false);
  });

  it('should render no report when the request fails', async () => {
    state.fetchResult = Promise.reject(new Error('nope'));
    const wrapper = await mountSwitcher();

    expect((wrapper.vm as any).isEnabled).toBe(false);
    expect(reportStub(wrapper).exists()).toBe(false);
  });

  it('should stop listening for switchTransitionsUrl once unmounted', async () => {
    const wrapper = await mountSwitcher();
    expect(listeners['Transitions.switchTransitionsUrl']).toHaveLength(1);

    wrapper.unmount();

    expect(listeners['Transitions.switchTransitionsUrl']).toHaveLength(0);
  });
});
