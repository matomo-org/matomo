/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent, ref } from 'vue';
import { mount } from '@vue/test-utils';
import useSparklineSlotSize, { MAX_DISPLAY_WIDTH } from './useSparklineSlotSize';

// The recording ResizeObserver stub from the test bootstrap (jsdom has none of its own).
interface ResizeObserverStub {
  observed: Element[];
  disconnectCount: number;
  trigger(entries: unknown[]): void;
}

function observers(): ResizeObserverStub[] {
  return (window as unknown as { __resizeObservers: ResizeObserverStub[] }).__resizeObservers;
}

// The composable measures again rather than reading what the observer hands it, so simulate a
// resize by changing what the slot measures and then firing the observer.
function resize(width: number, height = 40): void {
  setRect(width, height);
  const all = observers();
  all[all.length - 1].trigger([]);
  vi.advanceTimersByTime(150);
}

let rect = { width: 0, height: 40 };

function setRect(width: number, height = 40): void {
  rect = { width, height };
}

const Harness = defineComponent({
  setup() {
    const slot = ref<HTMLElement | null>(null);
    const { width, height } = useSparklineSlotSize(slot);

    return { slot, width, height };
  },
  template: '<div ref="slot"></div>',
});

describe('CoreVisualizations/useSparklineSlotSize', () => {
  beforeEach(() => {
    observers().length = 0;
    vi.useFakeTimers();
    setRect(0, 40);
    vi.spyOn(Element.prototype, 'getBoundingClientRect')
      .mockImplementation(() => rect as DOMRect);
  });

  afterEach(() => {
    vi.useRealTimers();
    // clearMocks is off in vitest.config.ts, so restore the spy by hand.
    vi.restoreAllMocks();
  });

  it('measures the slot synchronously on mount', () => {
    setRect(420);

    const wrapper = mount(Harness);

    expect(wrapper.vm.width).toBe(420);
    expect(wrapper.vm.height).toBe(40);
  });

  it('floors a fractional width so the image is never wider than its slot', () => {
    // Grid tracks often work out to fractions, and rounding up would overflow the slot.
    setRect(369.75);

    expect(mount(Harness).vm.width).toBe(369);
  });

  it('clamps the width to the size the server will render without silently truncating it', () => {
    setRect(MAX_DISPLAY_WIDTH + 500);

    expect(mount(Harness).vm.width).toBe(MAX_DISPLAY_WIDTH);
  });

  it('reports zero for a slot that cannot be measured yet', () => {
    // As in a collapsed widget or a hidden reporting tab.
    setRect(0, 0);

    const wrapper = mount(Harness);

    expect(wrapper.vm.width).toBe(0);
    expect(wrapper.vm.height).toBe(0);
  });

  it('picks up a size once an unmeasurable slot becomes visible', () => {
    // Both have to recover: the card waits for width and height, so a height stuck at 0 would
    // leave it empty forever.
    setRect(0, 0);
    const wrapper = mount(Harness);

    resize(420);

    expect(wrapper.vm.width).toBe(420);
    expect(wrapper.vm.height).toBe(40);
  });

  it('applies a resize only once the resize has settled', () => {
    setRect(420);
    const wrapper = mount(Harness);
    const all = observers();
    const observer = all[all.length - 1];

    setRect(560);
    observer.trigger([]);
    observer.trigger([]);
    expect(wrapper.vm.width).toBe(420);

    vi.advanceTimersByTime(150);
    expect(wrapper.vm.width).toBe(560);
  });

  it('keeps the last good width when the slot is detached and measures zero', () => {
    // The Dashboard detaches a widget while maximising it. Without this the sparkline would
    // disappear and refetch every time.
    setRect(420);
    const wrapper = mount(Harness);

    resize(0, 0);

    expect(wrapper.vm.width).toBe(420);
  });

  it('never tracks the height, which the slot fixes in CSS', () => {
    // Following the height would let the image resize the slot it was measured from.
    setRect(420);
    const wrapper = mount(Harness);

    resize(500, 90);

    expect(wrapper.vm.height).toBe(40);
  });

  it('does not re-read the height once it has a good measurement, even if it later reads zero', () => {
    setRect(420);
    const wrapper = mount(Harness);

    resize(420, 0);

    expect(wrapper.vm.height).toBe(40);
  });

  it('stops observing when the card is unmounted', () => {
    setRect(420);
    const wrapper = mount(Harness);
    const all = observers();
    const observer = all[all.length - 1];

    wrapper.unmount();

    expect(observer.disconnectCount).toBe(1);
  });
});
