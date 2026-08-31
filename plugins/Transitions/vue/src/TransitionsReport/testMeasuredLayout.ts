/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

type StubbedGlobal = { name: string; value: unknown; existed: boolean };

const stubbedGlobals: StubbedGlobal[] = [];

/** Jest has no stubGlobal, so record what was there and put it back in restoreStubbedGlobals(). */
export function stubGlobal(name: string, value: unknown): void {
  const target = globalThis as unknown as Record<string, unknown>;
  stubbedGlobals.push({ name, value: target[name], existed: name in target });
  target[name] = value;
}

/** Undoes every stubGlobal() of the current spec. Call it from afterEach. */
export function restoreStubbedGlobals(): void {
  const target = globalThis as unknown as Record<string, unknown>;
  while (stubbedGlobals.length) {
    const stub = stubbedGlobals.pop() as StubbedGlobal;
    if (stub.existed) {
      target[stub.name] = stub.value;
    } else {
      delete target[stub.name];
    }
  }
}

/**
 * jsdom reports every element as 0x0, leaving the ribbon layer nothing to lay out. Returns the spy,
 * so a test can also count measurements. Kept out of the shared bootstrap every plugin loads.
 */
export function stubElementRects(rect = { top: 0, height: 100, width: 100 }) {
  const domRect = {
    ...rect,
    left: 0,
    right: rect.width,
    bottom: rect.top + rect.height,
    x: 0,
    y: rect.top,
    toJSON: () => rect,
  } as DOMRect;

  return jest.spyOn(Element.prototype, 'getBoundingClientRect').mockReturnValue(domRect);
}

/** The layer measures inside requestAnimationFrame, which jsdom never fires on its own. */
export function useSynchronousFrames() {
  stubGlobal('requestAnimationFrame', (callback: FrameRequestCallback) => {
    callback(0);
    return 1;
  });
}

/** Holds scheduled frames instead of running them, so a spec can observe a pending frame. */
export function useHeldFrames(): FrameRequestCallback[] {
  let handle = 0;
  const held: FrameRequestCallback[] = [];
  stubGlobal('requestAnimationFrame', (callback: FrameRequestCallback) => {
    held.push(callback);
    handle += 1;
    return handle;
  });
  return held;
}
