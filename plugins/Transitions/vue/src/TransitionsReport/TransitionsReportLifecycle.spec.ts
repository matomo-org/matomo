/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
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
  translate: (key: string, ...args: string[]) => [key, ...args].join(':'),
}));

import TransitionsReport from './TransitionsReport.vue';
import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';
import { stubElementRects } from './testMeasuredLayout';

function reportWithPageviews(pageviews: number) {
  return {
    pageviews,
    exits: 1,
    directEntries: 1,
    groups: {
      previousPages: {
        total: 10,
        details: [{ url: 'http://example.org/a', referrals: 10 }],
      },
    },
  };
}

describe('Transitions/TransitionsReport lifecycle', () => {
  let backend: FakeTransitionsBackend;
  let measureSpy: ReturnType<typeof stubElementRects>;
  let resizeObservers: { targets: Element[]; disconnected: boolean }[];
  let resizeCallbacks: (() => void)[];
  let cancelled: number[];

  beforeEach(() => {
    postEvent.mockClear();
    measureSpy = stubElementRects();
    backend = installFakeTransitionsBackend(reportWithPageviews(100));

    resizeObservers = [];
    resizeCallbacks = [];
    cancelled = [];

    class FakeResizeObserver {
      private record: { targets: Element[]; disconnected: boolean };

      constructor(callback: () => void) {
        this.record = { targets: [], disconnected: false };
        resizeObservers.push(this.record);
        resizeCallbacks.push(callback);
      }

      observe(target: Element) {
        this.record.targets.push(target);
        this.record.disconnected = false;
      }

      disconnect() {
        this.record.targets = [];
        this.record.disconnected = true;
      }

      unobserve() {
        // not used by the composable
      }
    }

    vi.stubGlobal('ResizeObserver', FakeResizeObserver);
    vi.stubGlobal('cancelAnimationFrame', (handle: number) => {
      cancelled.push(handle);
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  /** Runs scheduled frames synchronously, so a rendered frame is enough for the ribbon layer. */
  function useSynchronousFrames() {
    vi.stubGlobal('requestAnimationFrame', (callback: FrameRequestCallback) => {
      callback(0);
      return 1;
    });
  }

  /** Holds scheduled frames, so a spec can observe a pending frame. */
  function useHeldFrames() {
    let handle = 0;
    const held: FrameRequestCallback[] = [];
    vi.stubGlobal('requestAnimationFrame', (callback: FrameRequestCallback) => {
      held.push(callback);
      handle += 1;
      return handle;
    });
    return held;
  }

  async function flush() {
    for (let i = 0; i < 5; i += 1) {
      // eslint-disable-next-line no-await-in-loop
      await nextTick();
    }
  }

  function mountReport(props = {}) {
    return mount(TransitionsReport as any, {
      props: {
        actionType: 'url',
        actionName: 'http://example.org/page',
        ...props,
      },
      global: { config: { globalProperties: { $sanitize: (value: string) => value } } },
    });
  }

  describe('request races', () => {
    it('should ignore a stale response that lands after a newer request started', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();

      await wrapper.setProps({ actionName: 'http://example.org/second' });
      expect(backend.loadCount()).toBe(2);

      // The newest request answers first, then the first one finally lands.
      backend.report = reportWithPageviews(222);
      backend.respondNewest();
      await flush();
      expect(wrapper.find('.transitionsCenterCard__pageviews').text()).toContain('222');

      backend.report = reportWithPageviews(111);
      backend.respond();
      await flush();

      // Still the newest result; the stale one was dropped.
      expect(wrapper.find('.transitionsCenterCard__pageviews').text()).toContain('222');
    });

    it('should ignore a stale error that lands after a newer request started', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();

      await wrapper.setProps({ actionName: 'http://example.org/second' });

      backend.respondNewest();
      await flush();
      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);

      backend.fail('NoDataForAction');
      await flush();

      expect(wrapper.find('.transitionsReport__error').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);
    });

    it('should start a request for each of the action type, name and override params', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();
      expect(backend.loadCount()).toBe(1);

      await wrapper.setProps({ actionType: 'title' });
      expect(backend.loadCount()).toBe(2);

      await wrapper.setProps({ actionName: 'Some title' });
      expect(backend.loadCount()).toBe(3);

      await wrapper.setProps({ overrideParams: { period: 'week' } });
      expect(backend.loadCount()).toBe(4);
    });
  });

  describe('unmount', () => {
    it('should not write state or post events when a response lands after unmount', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();

      wrapper.unmount();
      backend.respond();
      await flush();

      expect(postEvent).not.toHaveBeenCalled();
    });

    it('should not surface an error that lands after unmount', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();

      wrapper.unmount();

      expect(() => backend.fail('NoDataForAction')).not.toThrow();
    });

    it('should disconnect the resize observers', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();
      backend.respond();
      await flush();

      expect(resizeObservers).toHaveLength(2); // one layer per side
      expect(resizeObservers.every((observer) => observer.targets.length === 3)).toBe(true);

      wrapper.unmount();

      expect(resizeObservers.every((observer) => observer.disconnected)).toBe(true);
    });

    it('should cancel a frame that is still pending', async () => {
      const held = useHeldFrames();
      const wrapper = mountReport();
      backend.respond();
      await flush();

      expect(held.length).toBeGreaterThan(0);
      expect(cancelled).toHaveLength(0);

      wrapper.unmount();

      // One cancelled frame per ribbon layer.
      expect(cancelled).toHaveLength(2);
    });

    it('should not cancel anything when no frame is pending', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();
      backend.respond();
      await flush();

      wrapper.unmount();

      expect(cancelled).toHaveLength(0);
    });
  });

  describe('resize', () => {
    it('should re-measure when an observed element resizes', async () => {
      useSynchronousFrames();
      const wrapper = mountReport();
      backend.respond();
      await flush();

      const callsAfterLoad = measureSpy.mock.calls.length;
      expect(callsAfterLoad).toBeGreaterThan(0);

      resizeCallbacks.forEach((callback) => callback());
      await flush();

      expect(measureSpy.mock.calls.length).toBeGreaterThan(callsAfterLoad);
      wrapper.unmount();
    });
  });
});
