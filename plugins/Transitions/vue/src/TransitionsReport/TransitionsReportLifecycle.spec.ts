/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const postEvent = vi.fn();

// Pulled in dynamically: a vi.mock() factory is hoisted above every import in the file, so it
// cannot reach a top-level one.
vi.mock('CoreHome', async () => {
  const { coreHomeMock } = await import('./testCoreHomeMock');
  return coreHomeMock((...args: unknown[]) => postEvent(...args));
});

import { flushRibbons, mountTransitionsReport } from './testTransitionsReportHarness';

import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';
import { stubElementRects, useHeldFrames, useSynchronousFrames } from './testMeasuredLayout';

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



  describe('request races', () => {
    it('should ignore a stale response that lands after a newer request started', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();

      await wrapper.setProps({ actionName: 'http://example.org/second' });
      expect(backend.loadCount()).toBe(2);

      // The newest request answers first, then the first one finally lands.
      backend.report = reportWithPageviews(222);
      backend.respondNewest();
      await flushRibbons();
      expect(wrapper.find('.transitionsCenterCard__pageviews').text()).toContain('222');

      backend.report = reportWithPageviews(111);
      backend.respond();
      await flushRibbons();

      // Still the newest result; the stale one was dropped.
      expect(wrapper.find('.transitionsCenterCard__pageviews').text()).toContain('222');
    });

    it('should ignore a stale error that lands after a newer request started', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();

      await wrapper.setProps({ actionName: 'http://example.org/second' });

      backend.respondNewest();
      await flushRibbons();
      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);

      backend.fail('NoDataForAction');
      await flushRibbons();

      expect(wrapper.find('.transitionsReport__error').exists()).toBe(false);
      expect(wrapper.find('.transitionsReport__grid').exists()).toBe(true);
    });

    it('should start a request for each of the action type, name and override params', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();
      expect(backend.loadCount()).toBe(1);

      await wrapper.setProps({ actionType: 'title' });
      expect(backend.loadCount()).toBe(2);

      await wrapper.setProps({ actionName: 'Some title' });
      expect(backend.loadCount()).toBe(3);

      await wrapper.setProps({ overrideParams: { period: 'week' } });
      expect(backend.loadCount()).toBe(4);

      // Counting the loads does not say the right thing was asked for: without these an Overlay
      // or a deep-linked popover would happily fetch some other action's transitions.
      expect(backend.loads).toEqual([
        { actionType: 'url', actionName: 'http://example.org/page', overrideParams: {} },
        { actionType: 'title', actionName: 'http://example.org/page', overrideParams: {} },
        { actionType: 'title', actionName: 'Some title', overrideParams: {} },
        { actionType: 'title', actionName: 'Some title', overrideParams: { period: 'week' } },
      ]);
    });
  });

  describe('unmount', () => {
    it('should not post events when a response lands after unmount', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();

      wrapper.unmount();
      backend.respond();
      await flushRibbons();

      expect(postEvent).not.toHaveBeenCalled();
    });

    it('should disconnect the resize observers', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();
      backend.respond();
      await flushRibbons();

      expect(resizeObservers).toHaveLength(2); // one layer per side
      expect(resizeObservers.every((observer) => observer.targets.length === 3)).toBe(true);

      wrapper.unmount();

      expect(resizeObservers.every((observer) => observer.disconnected)).toBe(true);
    });

    it('should cancel a frame that is still pending', async () => {
      const held = useHeldFrames();
      const wrapper = mountTransitionsReport();
      backend.respond();
      await flushRibbons();

      expect(held.length).toBeGreaterThan(0);
      expect(cancelled).toHaveLength(0);

      wrapper.unmount();

      // One cancelled frame per ribbon layer.
      expect(cancelled).toHaveLength(2);
    });

    it('should not cancel anything when no frame is pending', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();
      backend.respond();
      await flushRibbons();

      wrapper.unmount();

      expect(cancelled).toHaveLength(0);
    });
  });

  describe('resize', () => {
    it('should re-measure when an observed element resizes', async () => {
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();
      backend.respond();
      await flushRibbons();

      const callsAfterLoad = measureSpy.mock.calls.length;
      expect(callsAfterLoad).toBeGreaterThan(0);

      resizeCallbacks.forEach((callback) => callback());
      await flushRibbons();

      expect(measureSpy.mock.calls.length).toBeGreaterThan(callsAfterLoad);
      wrapper.unmount();
    });

    it('should not re-measure when a row is hovered', async () => {
      // Highlighting is a class on the existing bands, so the geometry must not be invalidated by
      // it -- that is why highlight state is a prop of its own rather than part of `rows`.
      useSynchronousFrames();
      const wrapper = mountTransitionsReport();
      backend.respond();
      await flushRibbons();

      const callsAfterLoad = measureSpy.mock.calls.length;

      await wrapper.find('.transitionsRow').trigger('mouseenter');
      await flushRibbons();

      expect(wrapper.findAll('.transitionsRibbons__band--highlighted').length)
        .toBeGreaterThan(0);
      expect(measureSpy.mock.calls.length).toBe(callsAfterLoad);
      wrapper.unmount();
    });
  });
});
